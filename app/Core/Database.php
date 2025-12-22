<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        // Load config
        $config = require __DIR__ . '/../Config/app.php';
        $dbConfig = $config['db'];

        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true,
        ];

        try {
            $this->connection = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
        } catch (PDOException $e) {
            // In production, log this instead of showing
            if ($config['app']['debug']) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            } else {
                die("Database Connection Error");
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
