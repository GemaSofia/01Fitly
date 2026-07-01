<?php
$db = new SQLite3(__DIR__ . '/fitly.db');
// Crear tabla si no existe
$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT,
    correo TEXT UNIQUE,
    password TEXT,
    reset_code TEXT
)");

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

if (!$nombre || !$correo || !$password) {
    die("Error: Todos los campos son obligatorios.");
}

// Insertar usuario
$stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, password) VALUES (:n, :c, :p)");
$stmt->bindValue(':n', $nombre, SQLITE3_TEXT);
$stmt->bindValue(':c', $correo, SQLITE3_TEXT);
$stmt->bindValue(':p', $password, SQLITE3_TEXT);

if ($stmt->execute()) {

    // ============================
    // INSERTAR LOGROS AUTOMÁTICOS
    // ============================

    $usuario_id = $db->lastInsertRowID(); // obtener ID recién creado

    // Logros base
    $logros = ['7 días', '30 días', 'IMC x5', 'Primer IMC'];

    foreach ($logros as $l) {
        $stmt = $db->prepare("INSERT INTO logros (usuario_id, logro) VALUES (:u, :l)");
        $stmt->bindValue(':u', $usuario_id, SQLITE3_INTEGER);
        $stmt->bindValue(':l', $l, SQLITE3_TEXT);
        $stmt->execute();
    }

    // Redirigir después de crear todo correctamente
    echo "<script>
        alert('Registro exitoso. Ya puedes iniciar sesión.');
        window.location.href = 'login.html';
    </script>";

} else {
    echo "<h2 style='color:red;'>Error: Este correo ya está registrado.</h2>";
}
?>