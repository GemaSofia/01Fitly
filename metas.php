<?php
session_start();
$db = new SQLite3(__DIR__ . '/../fitly.db');

$usuario_id = $_SESSION['usuario_id'] ?? 0;

// ---- LISTAR
if ($_GET['accion'] === 'listar') {
    $res = $db->query("SELECT * FROM metas WHERE usuario_id = $usuario_id ORDER BY id DESC");
    $metas = [];

    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $metas[] = $row;
    }

    echo json_encode($metas);
    exit;
}

// ---- CREAR
if ($_POST['accion'] === 'crear') {
    $titulo = $_POST['titulo'];

    $stmt = $db->prepare("INSERT INTO metas (usuario_id, titulo) VALUES (:u, :t)");
    $stmt->bindValue(':u', $usuario_id);
    $stmt->bindValue(':t', $titulo);
    $stmt->execute();

    echo "ok";
    exit;
}

// ---- EDITAR META
if ($_POST['accion'] === 'editar') {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];

    $stmt = $db->prepare("UPDATE metas SET titulo = :t WHERE id = :id AND usuario_id = :u");
    $stmt->bindValue(':t', $titulo);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':u', $usuario_id);
    $stmt->execute();

    echo "ok";
    exit;
}

// ---- ELIMINAR
if ($_POST['accion'] === 'eliminar') {
    $id = $_POST['id'];
    $db->exec("DELETE FROM metas WHERE id = $id AND usuario_id = $usuario_id");
    echo "ok";
    exit;
}

// ---- COMPLETAR (BLOQUEA EL PROGRESO)
if ($_POST['accion'] === 'completar') {
    $id = $_POST['id'];
    $db->exec("UPDATE metas SET completado = 1, progreso = 100 WHERE id = $id AND usuario_id = $usuario_id");
    echo "ok";
    exit;
}

// ---- ACTUALIZAR PROGRESO (solo si NO está completada)
if ($_POST['accion'] === 'progreso') {
    $id = $_POST['id'];
    $p = $_POST['progreso'];

    $db->exec("UPDATE metas SET progreso = $p WHERE id = $id AND usuario_id = $usuario_id AND completado = 0");
    echo "ok";
    exit;
}
?>