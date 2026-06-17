<?php

$dbFile = __DIR__ . '/data/atlantis.sqlite';
$outFile = __DIR__ . '/database.sql';

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = [];
$sql[] = "-- SQLite Database Export";
$sql[] = "-- Generated: " . date('Y-m-d H:i:s');
$sql[] = "";
$sql[] = "BEGIN TRANSACTION;";

$tables = $pdo->query("
    SELECT name, sql
    FROM sqlite_master
    WHERE type='table'
      AND name NOT LIKE 'sqlite_%'
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($tables as $table) {
    $tableName = $table['name'];

    $sql[] = "";
    $sql[] = "-- Table: {$tableName}";
    $sql[] = $table['sql'] . ";";

    $rows = $pdo->query("SELECT * FROM \"$tableName\"")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $columns = array_map(fn($c) => "\"$c\"", array_keys($row));

        $values = [];
        foreach ($row as $value) {
            if ($value === null) {
                $values[] = "NULL";
            } else {
                $values[] = $pdo->quote($value);
            }
        }

        $sql[] =
            "INSERT INTO \"$tableName\" (" .
            implode(', ', $columns) .
            ") VALUES (" .
            implode(', ', $values) .
            ");";
    }
}

$sql[] = "";
$sql[] = "COMMIT;";

file_put_contents($outFile, implode("\n", $sql));

echo "Exported to: {$outFile}\n";