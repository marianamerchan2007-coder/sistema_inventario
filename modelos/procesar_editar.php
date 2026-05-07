<?php include("../config/conexion.php");
include('../includes/auth.php'); ?>

<?php 
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

    $nombre_imagen = time() . "_" . $_FILES["txtImagen"]["name"];
    $ruta_tmp = $_FILES["txtImagen"]["tmp_name"];

    $ruta_destino = __DIR__ . "/../image/" . $nombre_imagen;

    move_uploaded_file($ruta_tmp, $ruta_destino);

} else {
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
