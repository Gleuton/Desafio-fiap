<?php

use Core\DataBase\Builder;
use Core\DataBase\Connection;

return [
    Builder::class => static function () {
        return new Builder(Connection::connect());
    },
];