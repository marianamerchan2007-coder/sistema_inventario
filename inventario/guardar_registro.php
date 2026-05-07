<?php
include ("../config/conexion.php");
include("../includes/auth.php");

$id_rol = $_SESSION['rol'];
$id_sucursal_usuario = $_SESSION['sucursal'];

// SUCURSAL
$id_sucursal = $_POST['txtSucursal'] ?? null;

if ($id_rol == 2 && $id_sucursal != $id_sucursal_usuario) {
    die("No tienes permiso para usar otra sucursal");
}

// DATOS PRINCIPALES
$id_modelo  = $_POST['txtModelo'] ?? null;
$tallas     = $_POST['tallas'] ?? [];
$cantidades = $_POST['cantidades'] ?? [];

// VALIDACIÓN
if (!$id_modelo || !$id_sucursal || empty($tallas)) {
    die("Error: datos inválidos");
}

// OBTENER NOMBRE MODELO + SUCURSAL
$sql = "SELECT m.nombre_modelo, s.nombre_sucursal
        FROM modelo m
        INNER JOIN sucursal s ON s.id_sucursal = :sucursal
        WHERE m.id_modelo = :modelo";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    ":modelo" => $id_modelo,
    ":sucursal" => $id_sucursal
]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Error: modelo o sucursal no encontrados");
}

// GENERAR QR
$codigo_qr = str_replace(' ', '-', $data['nombre_modelo']) . '-' .
             str_replace(' ', '-', $data['nombre_sucursal']);

$total_registrado = 0;

// RECORRER TODAS LAS TALLAS
foreach ($tallas as $index => $id_talla) {

    $cantidad = $cantidades[$index] ?? 0;

    if (!$id_talla || $cantidad <= 0) continue;

    // VALIDAR TALLA
    $sql = "SELECT id_talla FROM talla WHERE id_talla = :talla";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([":talla" => $id_talla]);

    if (!$stmt->fetch()) continue;

    // VERIFICAR SI YA EXISTE
    $sql = "SELECT cantidad_disponible 
            FROM inventario 
            WHERE id_talla = :talla 
            AND id_sucursal = :sucursal
            AND id_modelo = :modelo";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":talla" => $id_talla,
        ":sucursal" => $id_sucursal,
        ":modelo" => $id_modelo
    ]);

    $existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existente) {

        // UPDATE
        $nueva = $existente['cantidad_disponible'] + $cantidad;

        $sql = "UPDATE inventario 
                SET cantidad_disponible = :cantidad,
                    fecha_ingreso = NOW(),
                    codigo_qr = :qr
                WHERE id_talla = :talla 
                AND id_sucursal = :sucursal 
                AND id_modelo = :modelo";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ":cantidad" => $nueva,
            ":qr" => $codigo_qr,
            ":talla" => $id_talla,
            ":sucursal" => $id_sucursal,
            ":modelo" => $id_modelo
        ]);

    } else {

        //  INSERT
        $sql = "INSERT INTO inventario 
            (cantidad_disponible, fecha_ingreso, codigo_qr, id_modelo, id_talla, id_sucursal)
            VALUES (:cantidad, NOW(), :qr, :modelo, :talla, :sucursal)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ":cantidad" => $cantidad,
            ":qr" => $codigo_qr,
            ":modelo" => $id_modelo,
            ":talla" => $id_talla,
            ":sucursal" => $id_sucursal
        ]);
    }

    $total_registrado += $cantidad;
}

// MENSAJE FINAL
$_SESSION['success'] = "Se registraron $total_registrado unidades del modelo "
    . $data['nombre_modelo'] . 
    " en " . $data['nombre_sucursal'];

// REDIRECCIÓN
header("Location: index.php");
exit();
