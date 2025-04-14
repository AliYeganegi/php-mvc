<?php
// src/Core/Database/Connection.php
namespace App\Core\Database;

class Connection
{
    protected static $pdo = null;
    
    protected static $config = [];

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public static function getPDO(): \PDO
    {
        if (self::$pdo === null) {
            self::connect();
        }
        
        return self::$pdo;
    }

    protected static function connect(): void
    {
        $driver = self::$config['driver'] ?? 'mysql';
        $host = self::$config['host'] ?? 'localhost';
        $port = self::$config['port'] ?? '3306';
        $database = self::$config['database'] ?? '';
        $username = self::$config['username'] ?? 'root';
        $password = self::$config['password'] ?? '';
        $charset = self::$config['charset'] ?? 'utf8mb4';
        $options = self::$config['options'] ?? [];
        
        // Default PDO options - good for security and performance
        $defaultOptions = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdoOptions = $options + $defaultOptions;
        
        try {
            $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
            self::$pdo = new \PDO($dsn, $username, $password, $pdoOptions);
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function beginTransaction(): bool
    {
        return self::getPDO()->beginTransaction();
    }
    
    public static function commit(): bool
    {
        return self::getPDO()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getPDO()->rollBack();
    }
}