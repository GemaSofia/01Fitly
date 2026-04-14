<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$db = new SQLite3(__DIR__ . '/../fitly.db');

$usuario_id = $_SESSION['usuario_id'];

$stmt = $db->prepare("
    SELECT peso, altura, imc, fecha 
    FROM imc 
    WHERE usuario_id = :u
    ORDER BY fecha DESC
");
$stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);

$result = $stmt->execute();

$historial = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $historial[] = $row;
}

echo json_encode($historial);
?>