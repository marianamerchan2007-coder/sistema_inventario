<?php
include("../config/conexion.php");

header('Content-Type: application/json');

$modelo = $_GET['modelo'] ?? null;
$sucursal = $_GET['sucursal'] ?? null;

if (!$modelo || !$sucursal) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT 
            i.id_inventario,
            t.numero_talla,
            i.cantidad_disponible
        FROM inventario i
        INNER JOIN talla t ON t.id_talla = i.id_talla
        WHERE i.id_modelo = :modelo
        AND i.id_sucursal = :sucursal
        AND i.cantidad_disponible > 0
        ORDER BY t.numero_talla ASC";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    ":modelo" => $modelo,
    ":sucursal" => $sucursal
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);