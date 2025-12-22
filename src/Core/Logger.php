<?php
/**
 * Logger Class
 * Application logging with levels and file rotation
 * 
 * @package ICOGroup
 */

namespace App\Core;

use App\Config\Config;

class Logger
{
    private const LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];

    private static ?Logger $instance = null;
    private Config $config;
    private string $logPath;
    private int $minLevel;

    private function __construct()
    {
        $this->config = Config::getInstance();
        $this->logPath = dirname(__DIR__, 2) . '/' . $this->config->get('LOG_PATH', 'storage/logs');
        $this->minLevel = self::LEVELS[strtoupper($this->config->get('LOG_LEVEL', 'debug'))] ?? 0;
        
        $this->ensureLogDirectory();
    }

    /**
     * Get Logger singleton instance
     */
    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void
    {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Write log entry
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $levelValue = self::LEVELS[$level] ?? 0;
        
        if ($levelValue < $this->minLevel) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        
        // Build log entry
        $entry = sprintf(
            "[%s] [%s] %s",
            $timestamp,
            $level,
            $message
        );

        if (!empty($context)) {
            $entry .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $entry .= PHP_EOL;

        // Determine log file
        $filename = $level === 'ERROR' || $level === 'CRITICAL' 
            ? "error-{$date}.log" 
            : "app-{$date}.log";

        // Write to file
        $filepath = $this->logPath . '/' . $filename;
        file_put_contents($filepath, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Debug level log
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Info level log
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Warning level log
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Error level log
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Critical level log
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    /**
     * Log exception
     */
    public function exception(\Throwable $e, array $context = []): void
    {
        $context['exception'] = [
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        
        $this->error($e->getMessage(), $context);
    }

    /**
     * Log API access
     */
    public function access(string $method, string $endpoint, int $statusCode, float $duration): void
    {
        $date = date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $entry = sprintf(
            "[%s] %s %s %s %d %.4fs \"%s\"\n",
            $timestamp,
            $ip,
            $method,
            $endpoint,
            $statusCode,
            $duration,
            $userAgent
        );

        $filepath = $this->logPath . "/access-{$date}.log";
        file_put_contents($filepath, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log audit event
     */
    public function audit(
        string $action,
        ?int $userId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $date = date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');
        
        $entry = [
            'timestamp' => $timestamp,
            'action' => $action,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        $filepath = $this->logPath . "/audit-{$date}.log";
        file_put_contents($filepath, $line, FILE_APPEND | LOCK_EX);
        
        // Also save to database if available
        try {
            $this->saveAuditToDb($entry);
        } catch (\Exception $e) {
            // Silently fail - file log is the backup
        }
    }

    /**
     * Save audit log to database
     */
    private function saveAuditToDb(array $entry): void
    {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO audit_logs 
                (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                VALUES 
                (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)";
        
        $db->query($sql, [
            ':user_id' => $entry['user_id'],
            ':action' => $entry['action'],
            ':entity_type' => $entry['entity_type'],
            ':entity_id' => $entry['entity_id'],
            ':old_values' => $entry['old_values'] ? json_encode($entry['old_values']) : null,
            ':new_values' => $entry['new_values'] ? json_encode($entry['new_values']) : null,
            ':ip_address' => $entry['ip'],
            ':user_agent' => $entry['user_agent']
        ]);
    }

    /**
     * Clean old log files
     */
    public function cleanOldLogs(int $daysToKeep = 30): int
    {
        $count = 0;
        $threshold = time() - ($daysToKeep * 86400);
        
        $files = glob($this->logPath . '/*.log');
        
        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
}
