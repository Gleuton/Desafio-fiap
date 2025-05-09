<?php

namespace Core\DataBase;

use PDOStatement;

interface ConnectionInterface
{
    public function prepare(string $sql): false|PDOStatement;
    public function lastInsertId(): string;
}