<?php

namespace Core\DataBase;

use PDO;

readonly class Builder implements BuilderInterface
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function findById(string $table, string $primaryKey, int $id, array $fields = ['*']): ?array
    {
        $fieldsString = implode(',', $fields);
        $sql = "SELECT $fieldsString FROM $table WHERE $primaryKey = :id";
        $results = $this->query($sql, [':id' => $id]);
        return $results ? $results[0] : null;
    }

    public function findBy(string $table, string $sqlFragment, array $params = []): ?array
    {
        $sql = "SELECT * FROM $table $sqlFragment";

        $results = $this->query($sql, $params);

        return $results ? $results[0] : null;
    }

    public function all(string $table, string $sqlFragment = '', array $params = []): array
    {
        $sql = "SELECT * FROM $table $sqlFragment";
        return $this->query($sql, $params);
    }

    public function query(string $sql, array $params = [], $fetch = PDO::FETCH_ASSOC): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll($fetch);
    }

    public function insert(string $table, array $data): ?int
    {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($values)";

        $stmt = $this->connection->prepare($sql);
        $success = $stmt->execute($data);
        return $success ? (int) $this->connection->lastInsertId() : null;
    }

    public function update(string $table, string $primaryKey, int $id, array $data): bool
    {
        $data[$primaryKey] = $id;

        $setClauses = [];
        foreach (array_keys($data) as $key) {
            $setClauses[] = "$key = :$key";
        }
        $sql = "UPDATE $table SET " . implode(', ', $setClauses);
        $sql .= " WHERE $primaryKey = :$primaryKey";

        return $this->execute($sql, $data);
    }

    public function delete(string $table, string $primaryKey, int $id): bool
    {
        $sql = "DELETE FROM $table WHERE $primaryKey = :id";
        return $this->execute($sql, [':id' => $id]);
    }

    public function execute(string $sql, array $params = []): bool
    {
        return $this->connection->prepare($sql)->execute($params);
    }
}