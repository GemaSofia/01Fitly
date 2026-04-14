<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conectar a SQLite
$db = new SQLite3('fitly.db');

// Correo ingresado por el usuario
$correo = $_POST['email'];

// Buscar usuario por correo
$stmt = $db->prepare("SELECT * FROM usuarios WHERE correo = :correo");
$stmt->bindValue(':correo', $correo, SQLITE3_TEXT);
$result = $stmt->execute();

$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    echo "No existe una cuenta con ese correo.";
    exit;
}

// Generar código de recuperación
$code = rand(100000, 999999);

// Guardarlo en la BD
$update = $db->prepare("UPDATE usuarios SET reset_code = :code WHERE correo = :correo");
$update->bindValue(':code', $code, SQLITE3_TEXT);
$update->bindValue(':correo', $correo, SQLITE3_TEXT);
$update->execute();

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // Tu correo + contraseña de aplicación
    $mail->Username = 'gemazamora71@gmail.com';
    $mail->Password = 'soszefpcusppnqps';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('gemazamora71@gmail.com', 'Recuperación FITLY');
    $mail->addAddress($correo);

    $mail->Subject = 'Código de recuperación - FITLY';
    $mail->Body = "Tu código de recuperación es: $code";

    $mail->send();

    
    echo "<script>
alert('Código enviado a tu correo');
window.location.href = 'reset.html?email=$correo';
</script>";

} catch (Exception $e) {
    echo "Error al enviar correo: {$mail->ErrorInfo}";
}