<?php
/**
 * Database Connection Class
 * Singleton PDO database connection with prepared statements
 * 
 * @package ICOGroup
 */

namespace App\Core;

use App\Config\Config;
use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private Config $config;

    private function __construct()
    {
        $this->config = Config::getInstance();
        $this->connect();
    }

    /**
     * Get Database singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     */
    private function connect(): void
    {
        $dbConfig = $this->config->getDatabase();
        
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['name'],
            $dbConfig['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']} COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->connection = new PDO(
                $dsn,
                $dbConfig['user'],
                $dbConfig['pass'],
                $options
            );
        } catch (PDOException $e) {
            if ($this->config->isDebug()) {
                throw new PDOException(
                    "Database Connection Error: " . $e->getMessage(),
                    (int) $e->getCode()
                );
            } else {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new PDOException("Database Connection Error", 500);
            }
        }
    }

    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query with named parameters
     * @param array $params Parameters to bind
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all results
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single row
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch single value
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return $this->query($sql, $params)->fetchColumn($column);
    }

    /**
     * Execute insert and return last insert ID
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Execute update/delete and return affected rows
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
