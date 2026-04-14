<?php
include 'conexion.php';

$email = $_POST['email'];
$code = $_POST['code'];

$sql = "SELECT * FROM usuarios WHERE email='$email' AND reset_code='$code'";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
    echo "ok";
} else {
    echo "Código incorrecto";
}
?>