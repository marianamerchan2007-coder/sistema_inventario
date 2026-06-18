<?php
session_start();
include('../config/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contrasenia = $_POST["contrasenia"] ?? "";
    try {
        $sql = "SELECT * FROM usuario WHERE contrasenia = :contrasenia LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":contrasenia", $contrasenia);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $_SESSION["admin"] = true;

            header("Location: ../admin/productos.php"); 
            exit;
        } else {
            // Login incorrecto
            $_SESSION["error_login"] = "Contraseña incorrecta.";
            header("Location: index.php"); 
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION["error_login"] = "Error en el sistema.";
        header("Location: index.php");
        exit;
    }
}
?>