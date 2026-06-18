<?php
date_default_timezone_set('America/Bogota');

$host = getenv('ADMIN_DB_HOST');
$port = getenv('ADMIN_DB_PORT');
$dbname = getenv('ADMIN_DB_NAME');
$user = getenv('ADMIN_DB_USER');
$password = getenv('ADMIN_DB_PASSWORD');

echo "<pre>";
echo "ADMIN_DB_NAME: " . $dbname;
echo "</pre>";
exit;

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
    die("Error de conexión");
}
