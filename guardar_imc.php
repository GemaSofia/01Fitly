<?php
session_start();
if (!isset($_SESSION['usuario_id'])) exit("NO LOGIN");

date_default_timezone_set('America/Mexico_City');

$db = new SQLite3(__DIR__ . '/../fitly.db');

$peso = $_POST['peso'];
$altura = $_POST['altura'];
$imc = $_POST['imc'];
$usuario_id = $_SESSION['usuario_id'];
$fecha = date("Y-m-d H:i:s");

$stmt = $db->prepare("INSERT INTO imc (usuario_id, peso, altura, imc, fecha)
VALUES (:u, :p, :a, :i, :f)");

$stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
$stmt->bindValue(':p', $peso, SQLITE3_FLOAT);
$stmt->bindValue(':a', $altura, SQLITE3_FLOAT);
$stmt->bindValue(':i', $imc, SQLITE3_FLOAT);
$stmt->bindValue(':f', $fecha, SQLITE3_TEXT);

$stmt->execute();

echo "IMC guardado";
?>