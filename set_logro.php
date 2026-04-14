<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit("NO LOGIN");

$db = new SQLite3(__DIR__ . '/../fitly.db');

$usuario_id = $_SESSION['usuario_id'];
$logro = $_POST['logro'];

$stmt = $db->prepare("
UPDATE logros 
SET desbloqueado = 1 
WHERE usuario_id = :u AND logro = :l
");

$stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
$stmt->bindValue(':l', $logro, SQLITE3_TEXT);

$stmt->execute();

echo "OK";