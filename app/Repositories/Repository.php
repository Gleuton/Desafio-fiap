<?php

namespace FiapAdmin\Repositories;

use Core\DataBase\BuilderInterface;

abstract class Repository
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function __construct(private readonly BuilderInterface $builder)
    {
    }

    protected function findById(int $id, array $fields = ['*']): ?array
    {
        return $this->builder->findById($this->table, $this->primaryKey, $id, $fields);
    }

    public function findBy(string $sqlFragment, array $params = []): ?array
    {
        return $this->builder->findBy($this->table, $sqlFragment, $params);
    }

    public function delete(int $id): bool
    {
        return $this->builder->delete($this->table, $this->primaryKey, $id);
    }

    public function insert(array $data): ?int
    {
        $data = $this->fillableData($data);

        return $this->builder->insert($this->table, $data);
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->fillableData($data);

        return $this->builder->update($this->table, $this->primaryKey, $id, $data);
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
        return $this->builder->execute($sql, $params);
    }

    public function query(string $sql, array $params = []): array
    {
        return $this->builder->query($sql, $params);
    }
}