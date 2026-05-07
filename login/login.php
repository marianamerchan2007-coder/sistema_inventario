<?php
session_start();
include("../config/conexion.php");

$usuario = $_POST['nombre_usuario'];
$clave = $_POST['contrasenia'];

$sql = "SELECT * FROM usuario WHERE nombre_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$usuario]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $clave === $user['contrasenia']) {

    $_SESSION['id_usuario'] = $user['id_usuario'];
    $_SESSION['nombre'] = $user['nombre_usuario'];
    $_SESSION['rol'] = $user['id_rol'];
    $_SESSION['sucursal'] = $user['id_sucursal'];
    $_SESSION['foto'] = $user['foto'];

    // REDIRECCIÓN
    if ($user['id_rol'] == 1) {
        //  JEFE
        header("Location: ../dashboard/index.php");
    } else {
        //  OPERARIO
        header("Location: ../inventario/index.php");
    }

    exit;

} else {
    
    $_SESSION['error_login'] = "Usuario o contraseña incorrectos";

    header("Location: index.php");
    exit;
}
?>

