<?php


require_once __DIR__ . '/../vendor/autoload.php';

use Core\DataBase\Connection;

try {
    $pdo = Connection::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "Tabela '$table' removida.\n";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $dumpPath = __DIR__ . '/../dump.sql';

    if (!file_exists($dumpPath)) {
        throw new Exception("Arquivo dump.sql não encontrado em: $dumpPath");
    }

    $sql = file_get_contents($dumpPath);
    $pdo->exec($sql);

    echo "\nBanco de dados recriado com sucesso ✅\n";
} catch (PDOException $e) {
    echo "Erro PDO: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Erro geral: " . $e->getMessage() . "\n";
}
