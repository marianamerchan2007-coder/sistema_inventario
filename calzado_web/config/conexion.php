<?php
date_default_timezone_set('America/Bogota');

echo "<pre>";
echo "__DIR__: " . __DIR__ . "<br>";
echo "autoload: " . __DIR__ . '/../vendor/autoload.php' . "<br>";
var_dump(file_exists(__DIR__ . '/../vendor/autoload.php'));
exit;

require_once __DIR__ . '/../vendor/autoload.php';

var_dump(class_exists('Dotenv\\Dotenv'));
exit;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];

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
