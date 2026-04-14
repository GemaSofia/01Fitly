<?php
$db = new SQLite3('fitly.db');

$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL,
    correo TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
)");


echo "Base de datos y tabla creada correctamente ✔";
?>
