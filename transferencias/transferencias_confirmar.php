<?php
include('../config/conexion.php');
session_start();

$id = $_GET['id'];

try {

    $conexion->beginTransaction();

    // 🔥 TRANSFERENCIA
    $sql = "SELECT * FROM transferencias WHERE id_transferencia = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $tr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tr) throw new Exception("Transferencia no existe");
    if ($tr['estado'] == 'confirmado') throw new Exception("Ya fue confirmada");

    // 🔥 DETALLES
    $sql = "SELECT * FROM transferencia_detalle WHERE id_transferencia = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$detalles) throw new Exception("Sin detalle");

    // 🔥 INVENTARIO ORIGEN + MODELO
    $sql = "SELECT i.*, m.nombre_modelo
            FROM inventario i
            INNER JOIN modelo m ON i.id_modelo = m.id_modelo
            WHERE i.id_inventario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tr['id_inventario_origen']]);
    $origen = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$origen) throw new Exception("Origen no existe");

    // 🔥 NOMBRE SUCURSAL DESTINO
    $sql = "SELECT nombre_sucursal FROM sucursal WHERE id_sucursal = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tr['id_sucursal_destino']]);
    $nombre_sucursal = $stmt->fetchColumn();

    if (!$nombre_sucursal) {
        throw new Exception("Sucursal destino no existe");
    }

    foreach ($detalles as $d) {

        $cantidad = $d['cantidad'];

        $stock_origen_antes = $origen['cantidad_disponible'];

        if ($stock_origen_antes < $cantidad) {
            throw new Exception("Stock insuficiente en origen");
        }

        // 🔴 DESCONTAR ORIGEN
        $sql = "UPDATE inventario
                SET cantidad_disponible = cantidad_disponible - ?
                WHERE id_inventario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$cantidad, $tr['id_inventario_origen']]);

        $stock_origen_despues = $stock_origen_antes - $cantidad;

        // 🔵 BUSCAR DESTINO
        $sql = "SELECT * FROM inventario
                WHERE id_modelo = ?
                AND id_talla = ?
                AND id_sucursal = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $origen['id_modelo'],
            $d['id_talla'],
            $tr['id_sucursal_destino']
        ]);

        $destino = $stmt->fetch(PDO::FETCH_ASSOC);

        $stock_destino_antes = $destino ? $destino['cantidad_disponible'] : 0;
        $stock_destino_despues = $stock_destino_antes + $cantidad;

        // 🔥 GENERAR QR
        $codigo_qr = $origen['nombre_modelo'] . '-' . $nombre_sucursal;

        // 🔵 SI EXISTE → SOLO SUMA
        if ($destino) {

            $sql = "UPDATE inventario
                    SET cantidad_disponible = cantidad_disponible + ?
                    WHERE id_inventario = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$cantidad, $destino['id_inventario']]);

        } else {

            // 🔵 CREAR NUEVO INVENTARIO CON QR
            $sql = "INSERT INTO inventario
                    (id_modelo, id_talla, id_sucursal, cantidad_disponible, fecha_ingreso, codigo_qr)
                    VALUES (?, ?, ?, ?, NOW(), ?)";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                $origen['id_modelo'],
                $d['id_talla'],
                $tr['id_sucursal_destino'],
                $cantidad,
                $codigo_qr
            ]);
        }

        // 🔥 RESTAURADO: GUARDAR EN DETALLE (CLAVE)
        $sql = "UPDATE transferencia_detalle
                SET stock_origen_antes = ?,
                    stock_origen_despues = ?,
                    stock_destino_antes = ?,
                    stock_destino_despues = ?
                WHERE id_transferencia = ? AND id_talla = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $stock_origen_antes,
            $stock_origen_despues,
            $stock_destino_antes,
            $stock_destino_despues,
            $id,
            $d['id_talla']
        ]);

        // 🔁 ACTUALIZAR VARIABLE LOCAL (importante)
        $origen['cantidad_disponible'] = $stock_origen_despues;
    }

    // 🔥 CONFIRMAR
    $sql = "UPDATE transferencias SET estado = 'confirmado' WHERE id_transferencia = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);

    $conexion->commit();

    $_SESSION['success'] = "Transferencia confirmada correctamente";
    header("Location: transferencias_pendientes.php");
    exit;

} catch (Exception $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    $_SESSION['error'] = $e->getMessage();
    header("Location: transferencias_pendientes.php");
    exit;
}
?>