<?php
session_start();
include("../config/conexion.php"); 
include("includes/auth.php");

require_once '../vendor/autoload.php';
require_once '../config/cloudinary.php';

use Cloudinary\Api\Upload\UploadApi;


if($_SERVER["REQUEST_METHOD"] == "POST"){

    $id = $_POST['id_producto'];
    $nombre = trim($_POST['nombre_producto']);
    $nombre = mb_convert_case($nombre,MB_CASE_TITLE,"UTF-8");
    $categoria = $_POST['id_categoria'];
    $subcategoria = $_POST['id_subcategoria'];
    $estado = $_POST['estado'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];


    $sql = "UPDATE producto SET
            nombre_producto = :nombre,
            id_categoria = :categoria,
            id_subcategoria = :subcategoria,
            estado = :estado,
            precio = :precio,
            descripcion = :descripcion
            WHERE id_producto = :id";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':subcategoria', $subcategoria);
    $stmt->bindParam(':precio', $precio);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':id', $id);
    $stmt->execute();


    // VALIDAR NOMBRE DUPLICADO EN MISMA CATEGORÍA Y SUBCATEGORÍA
    $sqlCheck = "SELECT COUNT(*) FROM producto 
                WHERE nombre_producto = :nombre 
                AND id_categoria = :categoria 
                AND id_producto != :id";

    $stmtCheck = $conexion->prepare($sqlCheck);
    $stmtCheck->bindParam(':nombre', $nombre);
    $stmtCheck->bindParam(':categoria', $categoria);
    $stmtCheck->bindParam(':id', $id);
    $stmtCheck->execute();

    $existe = $stmtCheck->fetchColumn();

    if($existe > 0){

        $_SESSION['error_nombre'] = "Ya existe un producto con ese nombre en esta categoría.";

        header("Location: editar_producto.php?id=$id");
        exit();
    }

    // SOLO DESPUÉS borrar e insertar
    $sqlDelete = "DELETE FROM producto_talla WHERE id_producto = :id";
    $stmtDelete = $conexion->prepare($sqlDelete);
    $stmtDelete->bindParam(':id', $id);
    $stmtDelete->execute();

    foreach($_POST['talla'] as $talla){

        $sqlTalla = "INSERT INTO producto_talla(numero_talla, id_producto)
                    VALUES(:talla, :id_producto)";

        $stmtTalla = $conexion->prepare($sqlTalla);
        $stmtTalla->bindParam(':talla', $talla);
        $stmtTalla->bindParam(':id_producto', $id);
        $stmtTalla->execute();
    }

    //Eliminar colores
    $sqlDeleteColor = "DELETE FROM producto_color WHERE id_producto = :id";
    $stmtDeleteColor = $conexion->prepare($sqlDeleteColor);
    $stmtDeleteColor->bindParam(':id', $id);
    $stmtDeleteColor->execute();

    //Guardar nuevos colores
    if(isset($_POST['colores'])){
        foreach($_POST['colores'] as $color){
            if(!empty($color)){
                $sqlColor = "INSERT INTO producto_color(nombre_color, id_producto)
                            VALUES(:color, :id_producto)";

                $stmtColor = $conexion->prepare($sqlColor);
                $stmtColor->bindParam(':color', $color);
                $stmtColor->bindParam(':id_producto', $id);
                $stmtColor->execute();
            }
        }
    }

    // actualizar orden imágenes
    $ordenesUsados = [];

    if(isset($_POST['orden_imagen'])){

        foreach($_POST['orden_imagen'] as $idImagen => $orden){

            $orden = (int)$orden;

            // evitar repetidos
            if(in_array($orden, $ordenesUsados)){

                $_SESSION['toast'] =
                    "No puedes repetir números de orden.";

                $_SESSION['toast_tipo'] = "danger";

                header("Location: editar_producto.php?id=$id&paso=2");
                exit();
            }

            $ordenesUsados[] = $orden;

            $sqlOrden = "UPDATE producto_imagen
                        SET orden = :orden
                        WHERE id_imagen = :id_imagen";

            $stmtOrden = $conexion->prepare($sqlOrden);

            $stmtOrden->bindParam(':orden', $orden);
            $stmtOrden->bindParam(':id_imagen', $idImagen);

            $stmtOrden->execute();
        }
    }

    // reset principal
    $sqlReset = "UPDATE producto_imagen
                SET principal = 0
                WHERE id_producto = :id";

    $stmtReset = $conexion->prepare($sqlReset);
    $stmtReset->bindParam(':id', $id);
    $stmtReset->execute();


    // nueva principal
    if(isset($_POST['imagen_principal'])){

        $idImagenPrincipal = $_POST['imagen_principal'];

        // principal = 1 y orden = 1
        $sqlPrincipal = "UPDATE producto_imagen
                        SET principal = 1,
                            orden = 1
                        WHERE id_imagen = :id_imagen";

        $stmtPrincipal = $conexion->prepare($sqlPrincipal);

        $stmtPrincipal->bindParam(
            ':id_imagen',
            $idImagenPrincipal
        );

        $stmtPrincipal->execute();
    }

    $imagenPrincipalActual = $_POST['imagen_principal'] ?? null;

    //eliminar imagen
    if(isset($_POST['eliminar_imagenes'])){
        foreach($_POST['eliminar_imagenes'] as $idImagen){
            $sqlBuscar = "SELECT ruta_imagen, public_id FROM producto_imagen WHERE id_imagen = :id";

            $stmtBuscar = $conexion->prepare($sqlBuscar);
            $stmtBuscar->bindParam(':id', $idImagen);
            $stmtBuscar->execute();
            $imagen = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

            if($imagen){

                // eliminar de Cloudinary
                if(!empty($imagen['public_id'])){

                    try{

                        (new UploadApi())->destroy(
                            $imagen['public_id']
                        );

                    }catch(Exception $e){

                        error_log($e->getMessage());
                    }
                }

                $sqlDelete = "DELETE FROM producto_imagen
                            WHERE id_imagen = :id";

                $stmtDelete = $conexion->prepare($sqlDelete);
                $stmtDelete->bindParam(':id', $idImagen);
                $stmtDelete->execute();
            }
        }
    }

    // nuevas imágenes
    if(!empty($_FILES['imagenes']['name'][0])){

        // obtener imágenes actuales
        $sqlActuales = "SELECT ruta_imagen
                        FROM producto_imagen
                        WHERE id_producto = :id";

        $stmtActuales = $conexion->prepare($sqlActuales);

        $stmtActuales->bindParam(':id', $id);

        $stmtActuales->execute();

        $imagenesActuales = $stmtActuales->fetchAll(PDO::FETCH_COLUMN);

        $nombresActuales = [];

        foreach($imagenesActuales as $ruta){

            $nombresActuales[] = basename($ruta);
        }

        // último orden
        $sqlMaxOrden = "SELECT MAX(orden)
                        FROM producto_imagen
                        WHERE id_producto = :id";

        $stmtMaxOrden = $conexion->prepare($sqlMaxOrden);

        $stmtMaxOrden->bindParam(':id', $id);

        $stmtMaxOrden->execute();

        $ultimoOrden = $stmtMaxOrden->fetchColumn();

        if(!$ultimoOrden){
            $ultimoOrden = 1;
        }

        foreach($_FILES['imagenes']['tmp_name'] as $key => $tmpName){

            if(empty($tmpName)){
                continue;
            }

            $nombreOriginal =
                $_FILES['imagenes']['name'][$key];

            // validar formato
            $mime = mime_content_type($tmpName);

            $permitidos = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if(!in_array($mime, $permitidos)){
                continue;
            }

            // validar tamaño
            if($_FILES['imagenes']['size'][$key] > 2 * 1024 * 1024){
                continue;
            }

            // evitar repetidas
            $imagenRepetida = false;

            foreach($nombresActuales as $nombreGuardado){

                if(str_contains($nombreGuardado, $nombreOriginal)){

                    $imagenRepetida = true;

                    break;
                }
            }

            if($imagenRepetida){
                continue;
            }

            $resultado = (new UploadApi())->upload(
                $tmpName,
                [
                    'folder' => 'calzado_web/productos'
                ]
            );
            
            $publicId = $resultado['public_id'];

            $ultimoOrden++;

            $sqlImagen = "INSERT INTO producto_imagen(
                            ruta_imagen,
                            public_id,
                            id_producto,
                            orden,
                            principal
                        )
                        VALUES(
                            :ruta,
                            :public_id,
                            :id_producto,
                            :orden,
                            0
                        )";

            $stmtImagen = $conexion->prepare($sqlImagen);

            $stmtImagen->bindParam(':ruta', $resultado['secure_url']);
            $stmtImagen->bindParam(':public_id', $publicId);
            $stmtImagen->bindParam(':id_producto', $id);
            $stmtImagen->bindParam(':orden', $ultimoOrden);

            $stmtImagen->execute();
        }
    }

    // verificar si todavía existe una principal
    $sqlVerificar = "SELECT COUNT(*) 
                    FROM producto_imagen
                    WHERE id_producto = :id
                    AND principal = 1";

    $stmtVerificar = $conexion->prepare($sqlVerificar);
    $stmtVerificar->bindParam(':id', $id);
    $stmtVerificar->execute();
    $tienePrincipal = $stmtVerificar->fetchColumn();

    // si NO hay principal
    if($tienePrincipal == 0){

        // buscar primera imagen disponible
        $sqlPrimera = "SELECT id_imagen
                    FROM producto_imagen
                    WHERE id_producto = :id
                    ORDER BY orden ASC
                    LIMIT 1";

        $stmtPrimera = $conexion->prepare($sqlPrimera);
        $stmtPrimera->bindParam(':id', $id);
        $stmtPrimera->execute();
        $primeraImagen = $stmtPrimera->fetch(PDO::FETCH_ASSOC);

        // convertir esa imagen en principal
        if($primeraImagen){

            $sqlNuevaPrincipal = "UPDATE producto_imagen
                                SET principal = 1, orden = 1
                                WHERE id_imagen = :id_imagen";

            $stmtNuevaPrincipal = $conexion->prepare($sqlNuevaPrincipal);

            $stmtNuevaPrincipal->bindParam(
                ':id_imagen',
                $primeraImagen['id_imagen']
            );

            $stmtNuevaPrincipal->execute();
        }
    }
}

// reorganizar órdenes automáticamente

$sqlReordenar = "SELECT id_imagen
                FROM producto_imagen
                WHERE id_producto = :id
                AND principal = 0
                ORDER BY orden ASC";

$stmtReordenar = $conexion->prepare($sqlReordenar);

$stmtReordenar->bindParam(':id', $id);

$stmtReordenar->execute();

$imagenesOrden = $stmtReordenar->fetchAll(PDO::FETCH_ASSOC);

$orden = 2;

foreach($imagenesOrden as $img){

    $sqlUpdateOrden = "UPDATE producto_imagen
                    SET orden = :orden
                    WHERE id_imagen = :id_imagen";

    $stmtUpdateOrden = $conexion->prepare($sqlUpdateOrden);

    $stmtUpdateOrden->bindParam(':orden', $orden);

    $stmtUpdateOrden->bindParam(
        ':id_imagen',
        $img['id_imagen']
    );

    $stmtUpdateOrden->execute();

    $orden++;
}

$_SESSION['toast'] = "Producto actualizado";
$_SESSION['toast_tipo'] = "success";

header("Location: productos.php");
exit();
?>