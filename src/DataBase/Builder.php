<?php

namespace Core\DataBase;

use PDO;

class Builder
{
    private string $table;
    private string $primaryKey = 'id';
    public array $fillable = [];

    public function __construct(private readonly PDO $connection)
    {
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function setPrimaryKey(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    public function setFillable(array $fillable): self
    {
        $this->fillable = $fillable;
        return $this;
    }

    public function findById(int $id, array $fields = ['*']): ?array
    {
        $fieldsString = implode(',', $fields);
        $sql = "SELECT $fieldsString FROM $this->table WHERE $this->primaryKey = :id";
        $results = $this->query($sql, [':id' => $id]);
        return $results ? $results[0] : null;
    }

    public function findBy(string $sqlFragment, array $params = []): ?array
    {
        $sql = "SELECT * FROM $this->table $sqlFragment";

        $results = $this->query($sql, $params);
        return $results ? $results[0] : null;
    }

    public function all(string $sqlFragment = '', array $params = []): array
    {
        $sql = "SELECT * FROM $this->table $sqlFragment";
        return $this->query($sql, $params);
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): ?int
    {
        $data = $this->fillableData($data);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $this->table ($columns) VALUES ($values)";

        $stmt = $this->connection->prepare($sql);
        $success = $stmt->execute($data);
        return $success ? (int) $this->connection->lastInsertId() : null;
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->fillableData($data);
        $data[$this->primaryKey] = $id;

        $setClauses = [];
        foreach (array_keys($data) as $key) {
            $setClauses[] = "$key = :$key";
        }

        $sql = "UPDATE $this->table SET " . implode(', ', $setClauses);
        $sql .= " WHERE $this->primaryKey = :$this->primaryKey";

        return $this->execute($sql, $data);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM $this->table WHERE $this->primaryKey = :id";
        return $this->execute($sql, [':id' => $id]);
    }

    private function fillableData(array $data): array
    {
        return array_filter(
            $data,
            function ($key) {
                return in_array($key, $this->fillable, true);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    public function execute(string $sql, array $params = []): bool
    {
        return $this->connection->prepare($sql)->execute($params);
    }
}