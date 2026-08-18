<?php

class Database
{
    private string $host = 'localhost';
    private string $database = 'crasm';
    private string $username = 'root';
    private string $password = '';

    public function connect(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";

            $pdo = new PDO($dsn, $this->username, $this->password);

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed.");
        }
    }
}