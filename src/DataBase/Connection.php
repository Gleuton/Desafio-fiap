<?php
namespace Core\DataBase;

use PDO;
use PDOStatement;

class Connection implements ConnectionInterface {
    private PDO $pdo;

    public function __construct() {
        $config = $this->loadConfig();
        $dsn = "mysql:host={$config->host};dbname={$config->dbname};charset=utf8mb4";
        $this->pdo = new PDO(
            $dsn,
            $config->user,
            $config->password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    private function loadConfig(): object {
        return json_decode(
            file_get_contents(__DIR__ . '/../../env.json'),
            false,
            512,
            JSON_THROW_ON_ERROR
        );
    }
    public function prepare(string $sql): false|PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}