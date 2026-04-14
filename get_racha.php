<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit("NO LOGIN");

$db = new SQLite3(__DIR__ . '/../fitly.db');
$usuario = $_SESSION['usuario_id'];

$stmt = $db->prepare("SELECT racha, ultima_fecha FROM rachas WHERE usuario_id = :u");
$stmt->bindValue(':u', $usuario, SQLITE3_INTEGER);
$res = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($res) {
    echo json_encode($res);
} else {
    // Si no existe, crear registro inicial
    $stmt = $db->prepare("INSERT INTO rachas (usuario_id, racha, ultima_fecha)
                          VALUES (:u, 0, NULL)");
    $stmt->bindValue(':u', $usuario, SQLITE3_INTEGER);
    $stmt->execute();
    echo json_encode(["racha" => 0, "ultima_fecha" => null]);
}
?>