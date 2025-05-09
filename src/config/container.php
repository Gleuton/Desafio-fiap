<?php

use Core\DataBase\Builder;
use Core\DataBase\BuilderInterface;
use Core\DataBase\Connection;
use Core\DataBase\ConnectionInterface;

use function DI\autowire;

return [
    BuilderInterface::class => autowire(Builder::class),
    ConnectionInterface::class => autowire(Connection::class),
];