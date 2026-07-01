<?php
session_start();


$db = new SQLite3(__DIR__ . '/fitly.db');

$correo = $_POST['correo'];
$password = $_POST['password'];

$stmt = $db->prepare("SELECT * FROM usuarios WHERE correo = :c");
$stmt->bindValue(':c', $correo, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    
    // GUARDAR SESIÓN REAL
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];
    $_SESSION['usuario_correo'] = $user['correo'];

    // REDIRECCIÓN AL DASHBOARD REAL
    header("Location: ../dashboard.php");
    exit();
    
} else {
    // SI FALLA EL LOGIN
    header("Location: ../login.html?error=1");
    exit();
}
?>