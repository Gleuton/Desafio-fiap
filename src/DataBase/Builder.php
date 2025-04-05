<?php

namespace Core\DataBase;

use PDO;

class Builder
{
    private string $table;
    private string $primaryKey = 'id';
    private array $fillable = [];

    public function __construct(private readonly PDO $connection)
    {
    }

    public function setTable($table): self
    {
        $this->table = $table;
        return $this;
    }

    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    public function setFillable(array $fillable): void
    {
        $this->fillable = $fillable;
    }

    public function findById(string $id)
    {
        $sql = "SELECT * FROM $this->table WHERE $this->primaryKey = '$id'";

        return $this->connection->query($sql)->fetchObject();
    }

    public function findBy(string $filters)
    {
        $sql = "SELECT * FROM {$this->table}";

        $sql .= ' ' . $filters;

        return $this->connection->query($sql)->fetchObject();
    }

    public function all(string $filter = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($filter)) {
            $sql .= ' ' . $filter;
        }

        return $this->connection->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function insert(array $data): bool
    {
        $data = $this->fillableData($data);

        $columns = implode(', ', array_keys($data));
        $values = implode(', :', array_keys($data));

        $sql = "INSERT INTO $this->table ($columns) VALUES (:$values)";

        return $this->connection->prepare($sql)->execute($data);
    }

    public function update(string $id, array $data): bool
    {
        $data = $this->fillableData($data);
        $columns = '';

        foreach (array_keys($data) as $key) {
            $columns .= "$key=:$key,";
        }

        $data[$this->primaryKey] = $id;
        $columns = substr($columns, 0, -1);

        $sql = "UPDATE $this->table SET $columns";
        $sql .= " WHERE $this->primaryKey=:$this->primaryKey";
        return $this->connection->prepare($sql)->execute($data);
    }

    public function delete(string $id): void
    {
        $sql = "DELETE FROM $this->table WHERE $this->primaryKey = ?";
        $this->connection->prepare($sql)->execute([$id]);
    }

    private function fillableData(array $data): array
    {
        return array_filter($data, function ($key) {
            return in_array($key, $this->fillable, true);
        }, ARRAY_FILTER_USE_KEY);
    }
}