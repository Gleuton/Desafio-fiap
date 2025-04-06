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

    public function findById(int $id): ?array
    {
        return $this->conn->findById($id);
    }

    public function findBy(string $sqlFragment, array $params = []): ?array
    {
        return $this->conn->findBy($sqlFragment, $params);
    }


}