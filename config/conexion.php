<?php
date_default_timezone_set('America/Bogota');

$host = "mysql-f5210-marianamerchan2007-8a29.g.aivencloud.com";
$port = "21591";
$dbname = "defaultdb";
$user = "avnadmin";
$password = "";

try {

    $conexion = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $user,
        $password,
        [
            PDO::MYSQL_ATTR_SSL_CA => __DIR__ . "/certificados/ca.pem",
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    $conexion->exec("SET time_zone = '-05:00'");

} catch (PDOException $e) {
    die($e->getMessage());
}
?>