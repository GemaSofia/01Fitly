<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$db = new SQLite3(__DIR__ . '/../fitly.db');

$usuario_id = $_SESSION['usuario_id'];

$stmt = $db->prepare("SELECT logro, desbloqueado FROM logros WHERE usuario_id = :u");
$stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);

$res = $stmt->execute();

$logros = [];

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $logros[] = $row;
}

echo json_encode($logros);