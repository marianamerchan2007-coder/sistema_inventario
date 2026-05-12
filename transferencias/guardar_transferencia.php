<?php
include('../config/conexion.php');
include("../includes/auth.php");

try {

    $id_origen = $_POST['id_inventario_origen'] ?? null;
    $id_sucursal_destino = $_POST['id_sucursal_destino'] ?? null;
    $cantidad = $_POST['cantidad'] ?? null;

    if (!$id_origen || !$id_sucursal_destino || !$cantidad) {
        throw new Exception("Faltan datos del formulario");
    }

    $conexion->beginTransaction();

    // =========================
    // 1. OBTENER INVENTARIO ORIGEN
    // =========================
    $sql = "SELECT id_modelo, id_talla, id_sucursal, cantidad_disponible
            FROM inventario
            WHERE id_inventario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_origen]);
    $origen = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$origen) {
        throw new Exception("Inventario origen no existe");
    }

    if ($cantidad <= 0) {
        throw new Exception("Cantidad inválida");
    }

    if ($cantidad > $origen['cantidad_disponible']) {
        
    $_SESSION['error'] = "Stock insuficiente para la transferencia";

    $conexion->rollBack(); 

    header("Location: nueva_transferencia.php");
    exit();
    }  

    if ($origen['id_sucursal'] == $id_sucursal_destino) {
        throw new Exception("No puedes transferir a la misma sucursal");
    }

    // =========================
    // 2. CREAR TRANSFERENCIA (CABECERA)
    // =========================
    $sql = "INSERT INTO transferencias
            (fecha_transferencia, id_inventario_origen, id_sucursal_destino, estado)
            VALUES (NOW(), ?, ?, 'pendiente')";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_origen, $id_sucursal_destino]);

    $id_transferencia = $conexion->lastInsertId();


    $sql = "SELECT m.nombre_modelo, t.numero_talla
        FROM modelo m
        INNER JOIN talla t ON t.id_talla = ?
        WHERE m.id_modelo = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$origen['id_talla'], $origen['id_modelo']]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);


    $_SESSION['success'] = "Esperando confirmación de la transferencia: Modelo "
        . $info['nombre_modelo'] . 
        " | Talla " . $info['numero_talla'] . 
        " | Local";

    // =========================
    // 3. CREAR DETALLE
    // =========================
    $sql = "INSERT INTO transferencia_detalle
            (id_transferencia, id_talla, cantidad)
            VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $id_transferencia,
        $origen['id_talla'],
        $cantidad
    ]);

    $conexion->commit();

    header("Location: index.php");
    exit;

} catch (Exception $e) {

    $conexion->rollBack();
    echo "Error: " . $e->getMessage();
}
?>


