<?php

namespace Core\DataBase;

use PDO;

class Connection
{
    private static $instance = null;

    private function __construct()
    {
    }

    private static function config(): object
    {
        $file = file_get_contents(__DIR__ . '/../../env.json');

        return json_decode($file, false, 512, JSON_THROW_ON_ERROR);
    }

    public static function connect(): PDO
    {
        $conn = "mysql:";
        $conn .= "host=" . self::config()->host . ";";
        $conn .= "dbname=" . self::config()->dbname . ";";

        if (is_null(self::$instance)) {
            self::$instance = new PDO(
                $conn,
                self::config()->user,
                self::config()->password,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
        }
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}