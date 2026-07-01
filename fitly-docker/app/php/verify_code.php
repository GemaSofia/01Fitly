
<?php
session_start();

if (!isset($_SESSION['codigo']) || !isset($_SESSION['correo_reset'])) {
    die("La sesión expiró. Solicita un nuevo código.");
}

$codigoIngresado = trim($_POST['codigo']);

if ($codigoIngresado == $_SESSION['codigo']) {

    // Código correcto
    header("Location: reset.html");
    exit();

} else {

    echo "<script>
        alert('Código incorrecto.');
        window.location.href='forgot.html';
    </script>";

}
?>