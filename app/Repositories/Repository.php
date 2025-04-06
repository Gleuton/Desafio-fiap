<?php

namespace FiapAdmin\Repositories;

use Core\DataBase\Builder;
use Core\DataBase\Connection;

abstract class Repository
{
    protected readonly Builder $conn;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct()
    {
        $conn = Connection::connect();
        $conn->setAttribute(
            \PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION
        );

        $this->conn = new Builder($conn);

        $this->conn->setTable($this->table);
        $this->conn->setPrimaryKey($this->primaryKey);
        $this->conn->setFillable($this->fillable);
    }

    protected function findById(int $id, array $fields = ['*']): ?array
    {
        return $this->conn->findById($id, $fields);
    }

    public function findBy(string $sqlFragment, array $params = []): ?array
    {
        return $this->conn->findBy($sqlFragment, $params);
    }

    public function delete(int $id): bool
    {
        return $this->conn->delete($id);
    }
}