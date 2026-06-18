<?php
session_start();
include("../config/conexion.php"); 
include("includes/auth.php");

require_once '../vendor/autoload.php';
require_once '../config/cloudinary.php';

use Cloudinary\Api\Upload\UploadApi;


if(!isset($_GET['id'])){
    header("Location:productos.php");
    exit;
}

$id = $_GET['id'];

//eliminar imagenes
$sqlImagen = "SELECT public_id 
              FROM producto_imagen 
              WHERE id_producto = :id";

$stmtImagen = $conexion->prepare($sqlImagen);
$stmtImagen->bindParam(':id', $id);
$stmtImagen->execute();

$imagenes = $stmtImagen->fetchAll(PDO::FETCH_ASSOC);

//BORRAR EN CLOUDINARY
$uploadApi = new UploadApi();

foreach($imagenes as $img){

    if(!empty($img['public_id'])){

        try {
            $uploadApi->destroy($img['public_id']);
        } catch(Exception $e){
            // opcional: log del error
        }
    }
}


//tablas relacionadas
$sqlDeleteImagenes = "DELETE FROM producto_imagen WHERE id_producto = :id";
$stmtDeleteImagenes = $conexion->prepare($sqlDeleteImagenes);
$stmtDeleteImagenes->bindParam(':id', $id);
$stmtDeleteImagenes->execute();

$sqlDeleteTalla = "DELETE FROM producto_talla WHERE id_producto = :id";
$stmtDeleteTalla = $conexion->prepare($sqlDeleteTalla);
$stmtDeleteTalla->bindParam(':id', $id);
$stmtDeleteTalla->execute();

$sqlDeleteColor = "DELETE FROM producto_color WHERE id_producto = :id";
$stmtDeleteColor = $conexion->prepare($sqlDeleteColor);
$stmtDeleteColor->bindParam(':id', $id);
$stmtDeleteColor->execute();

$sqlDeleteProducto = "DELETE FROM producto WHERE id_producto = :id";
$stmtDeleteProducto = $conexion->prepare($sqlDeleteProducto);
$stmtDeleteProducto->bindParam(':id', $id);
$stmtDeleteProducto->execute();

$_SESSION['toast'] = "Producto eliminado correctamente ";
$_SESSION['toast_tipo'] = "success";

header("Location: productos.php");
exit();
?>
