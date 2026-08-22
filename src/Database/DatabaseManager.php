<?php

namespace App\Database;

use App\Config\DatabaseConfig;
use PDO;
use PDOException;

class DatabaseManager
{
    private DatabaseConfig $config;
    private ?PDO $pdo = null;

    public function __construct(?DatabaseConfig $config = null)
    {
        $this->config = $config ?? new DatabaseConfig();
    }

    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $dsn = $this->config->getDsn();
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $this->config->getUser(), $this->config->getPassword(), $options);
        }
        return $this->pdo;
    }

    public function createTable(): bool
    {
        $pdo = $this->getPdo();

        $sql = "
            DROP TABLE IF EXISTS users CASCADE;
            CREATE TABLE users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE UNIQUE INDEX idx_users_email ON users (email);
        ";

        try {
            $pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            throw new \RuntimeException('Failed to create users table: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getExistingEmails(): array
    {
        try {
            $pdo = $this->getPdo();
            $stmt = $pdo->query('SELECT LOWER(email) AS email FROM users');
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
}
