<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "No has iniciado sesión";
    exit();
}

$db = new SQLite3(__DIR__ . '/../fitly.db');

$usuario_id = $_SESSION['usuario_id'];
$hoy = date("Y-m-d");

// Buscar racha actual
$stmt = $db->prepare("SELECT * FROM rachas WHERE usuario_id = :u");
$stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$racha = $result->fetchArray(SQLITE3_ASSOC);

if ($racha) {
    $ultima = $racha['ultima_fecha'];
    $ayer = date("Y-m-d", strtotime("-1 day"));

    if ($ultima == $hoy) {
        echo "✔ Ya registraste hoy";
        exit();
    }

    if ($ultima == $ayer) {
        $nueva = $racha['racha'] + 1;
    } else {
        $nueva = 1;
    }

    $stmt = $db->prepare("UPDATE rachas SET racha = :r, ultima_fecha = :f WHERE usuario_id = :u");
    $stmt->bindValue(':r', $nueva, SQLITE3_INTEGER);
    $stmt->bindValue(':f', $hoy, SQLITE3_TEXT);
    $stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
    $stmt->execute();

} else {
    $stmt = $db->prepare("INSERT INTO rachas (usuario_id, racha, ultima_fecha) VALUES (:u, 1, :f)");
    $stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
    $stmt->bindValue(':f', $hoy, SQLITE3_TEXT);
    $stmt->execute();

    $nueva = 1;
}

echo "🔥 Racha actual: $nueva días";
?>