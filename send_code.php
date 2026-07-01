<?php
require '../vendor/autoload.php';
include 'conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_POST['email'];

// 1. Verificar si el correo existe
$sql = "SELECT * FROM usuarios WHERE email='$email'";
$res = $conn->query($sql);

if ($res->num_rows == 0) {
    echo "Correo no registrado";
    exit;
}

// 2. Generar código de 6 dígitos
$code = rand(100000, 999999);

// 3. Guardarlo en la BD
$conn->query("UPDATE usuarios SET reset_code='$code' WHERE email='$email'");

// 4. Enviar correo
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "TU_CORREO@gmail.com";
    $mail->Password = "TU_APP_PASSWORD"; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom("TU_CORREO@gmail.com", "Fitly Recuperación");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Código de recuperación - Fitly";
    $mail->Body = "<h2>Tu código es: <strong>$code</strong></h2>";

    $mail->send();
    echo "ok";

} catch (Exception $e) {
    echo "Error al enviar: {$mail->ErrorInfo}";
}
?>
