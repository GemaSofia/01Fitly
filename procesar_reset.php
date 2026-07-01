<?php
$db = new SQLite3('fitly.db');

$correo = $_POST['correo'];
$code = $_POST['code'];
$newpass = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Buscar usuario con correo + código
$stmt = $db->prepare('SELECT * FROM usuarios WHERE correo = :correo AND reset_code = :code');
$stmt->bindValue(':correo', $correo, SQLITE3_TEXT);
$stmt->bindValue(':code', $code, SQLITE3_TEXT);
$result = $stmt->execute();

$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    echo "<script>
            alert('Código incorrecto o no coincide con el correo.');
            window.location.href = 'reset.html?email=$correo';
          </script>";
    exit;
}

// Actualizar contraseña
$update = $db->prepare('UPDATE usuarios SET password = :pass, reset_code = NULL WHERE correo = :correo');
$update->bindValue(':pass', $newpass, SQLITE3_TEXT);
$update->bindValue(':correo', $correo, SQLITE3_TEXT);
$update->execute();

// Redirección automática
echo "<script>
alert('Contraseña actualizada correctamente. Serás redirigido al inicio de sesión.');
window.location.href = 'login.html';
</script>";
?>