<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\DataBase\Builder;
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/config/container.php');

try {
    $container = $containerBuilder->build();
    $connection = $container->get(Builder::class);

    $connection->execute("SET FOREIGN_KEY_CHECKS = 0");

    $tables = $connection->query("SHOW TABLES", [], PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $connection->execute("DROP TABLE IF EXISTS `$table`");
        echo "Tabela '$table' removida.\n";
    }

    $connection->execute("SET FOREIGN_KEY_CHECKS = 1");

    $dumpPath = __DIR__ . '/../dump.sql';

    if (!file_exists($dumpPath)) {
        throw new Exception("Arquivo dump.sql não encontrado em: $dumpPath");
    }

    $sql = file_get_contents($dumpPath);
    $connection->execute($sql);

    echo "\nBanco de dados recriado com sucesso ✅\n";
} catch (PDOException $e) {
    echo "Erro PDO: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Erro geral: " . $e->getMessage() . "\n";
}
