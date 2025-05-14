<?php

namespace Core\DataBase;

use PDO;

interface BuilderInterface
{
    public function findById(string $table, string $primaryKey, int $id, array $fields = ['*']): ?array;
    public function findBy(string $table, string $sqlFragment, array $params = []): ?array;
    public function all(string $table, string $sqlFragment = '', array $params = []): array;
    public function delete(string $table, string $primaryKey, int $id): bool;
    public function query(string $sql, array $params = [], $fetch = PDO::FETCH_ASSOC): mixed;
    public function insert(string $table, array $data): ?int;
    public function update(string $table, string $primaryKey, int $id, array $data): bool;
    public function execute(string $sql, array $params = []): bool;
}