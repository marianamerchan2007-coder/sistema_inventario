<?php include("../config/conexion.php");
include('../includes/auth.php'); ?>

<?php 
require '../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dwpgi7rnl',
        'api_key'    => '155544861992565',
        'api_secret' => 'TU_API_SECRET_COMPLETO'
    ],
    'url' => [
        'secure' => true
    ]
]);


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Acceso no permitido");
}


$id = $_POST["txtId"] ?? '';
$nombre = $_POST["txtNombre"] ?? '';
$descripcion = $_POST["txtDescripcion"] ?? '';
$origen = $_POST["txtOrigen"] ?? '';
$tallas = $_POST["txtTalla"] ?? '';


// IMAGEN
if (!empty($_FILES["txtImagen"]["name"])) {

    try {

        $subida = (new UploadApi())->upload(
            $_FILES["txtImagen"]["tmp_name"]
        );

        // URL CLOUDINARY
        $nombre_imagen = $subida['secure_url'];

    } catch (Exception $e) {

        die("Error al subir imagen: " . $e->getMessage());
    }

} else {

    // Mantener imagen actual
    $nombre_imagen = $_POST["imagen_actual"];
}

// UPDATE MODELO
$sql = "UPDATE modelo 
        SET nombre_modelo = :nombre,
            descripcion = :descripcion,
            origen_producto = :origen,
            imagen = :imagen
        WHERE id_modelo = :id";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":nombre" => $nombre,
    ":descripcion" => $descripcion,
    ":origen" => $origen,
    ":imagen" => $nombre_imagen,
    ":id" => $id
]);

// TALLAS
$sql = "INSERT IGNORE INTO talla (numero_talla, id_modelo) VALUES (:talla, :id)";
$stmt = $conexion->prepare($sql);

foreach ($tallas as $talla) {
    $stmt->execute([
        ":talla" => $talla,
        ":id" => $id
    ]);
}

header("Location: index.php");
exit();
?>
