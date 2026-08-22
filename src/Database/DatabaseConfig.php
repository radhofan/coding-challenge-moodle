<?php

namespace App\Database;

class DatabaseConfig
{
    private string $host;
    private int $port;
    private string $database;
    private string $user;
    private string $password;

    public function __construct(
        ?string $host = null,
        ?int $port = null,
        ?string $database = null,
        ?string $user = null,
        ?string $password = null
    ) {
        $this->host = $host ?? getenv('DB_HOST') ?: '127.0.0.1';
        $this->port = $port ?? (int)(getenv('DB_PORT') ?: 5432);
        $this->database = $database ?? getenv('DB_DATABASE') ?: 'user_import';
        $this->user = $user ?? getenv('DB_USER') ?: 'postgres';
        $this->password = $password ?? getenv('DB_PASSWORD') ?: 'postgres';
    }

    public function getDsn(): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $this->host,
            $this->port,
            $this->database
        );
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
