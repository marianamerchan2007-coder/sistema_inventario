<?php
session_start();
include('../config/conexion.php');

try {

    $conexion->beginTransaction();

    $id_sucursal_usuario = $_SESSION['sucursal'];

    $id_inventario = $_POST['id_inventario'];
    $cantidad = $_POST['txtCantidad'];
    $id_tipo_venta = $_POST['id_tipo_venta'];

    // obtener inventario
    $sql = "SELECT id_sucursal, cantidad_disponible 
            FROM inventario 
            WHERE id_inventario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_inventario]);

    $inv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inv) {
        throw new Exception("Inventario no encontrado");
    }

    // 🚫 sucursal incorrecta
    $id_usuario = $_SESSION['id_usuario'];
    $id_sucursal_usuario = $_SESSION['sucursal'];

    // jefe puede todo
    if ($id_usuario != 1) {

        // validar contra sucursal del inventario
        if ($inv['id_sucursal'] != $id_sucursal_usuario) {

            $_SESSION['error'] = " No autorizado para vender en otra sucursal";

            $conexion->rollBack();
            header("Location: registrar_venta.php");
            exit();
        }
    }

    // 🚫 stock insuficiente
    if ($inv['cantidad_disponible'] < $cantidad) {

        $_SESSION['error'] = " Stock insuficiente. Intente registrar la venta nuevamente. <br>Cantidad disponible: " . $inv['cantidad_disponible'];

        $conexion->rollBack();
        header("Location: registrar_venta.php");
        exit();
    }

    // actualizar inventario
    $sql = "UPDATE inventario 
            SET cantidad_disponible = cantidad_disponible - ? 
            WHERE id_inventario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$cantidad, $id_inventario]);

    // guardar venta
    $sql = "INSERT INTO ventas 
            (id_inventario, cantidad_vendida, id_tipo_venta, fecha_venta)
            VALUES (?, ?, ?, NOW())";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $id_inventario,
        $cantidad,
        $id_tipo_venta
    ]);

    $conexion->commit();

    $_SESSION['success'] = "Venta registrada correctamente";
    header("Location: index.php");
    exit;

} catch (Exception $e) {

    $conexion->rollBack();
    $_SESSION['error'] = $e->getMessage();

    header("Location: registrar_venta.php");
    exit();
}