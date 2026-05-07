<?php
include("../config/conexion.php");
include("../includes/auth.php");

header('Content-Type: application/json');

$modelo = $_GET['modelo'] ?? null;
$sucursal = $_GET['sucursal'] ?? null;

if (!$modelo || !$sucursal) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT 
            t.id_talla,
            t.numero_talla,
            COALESCE(i.cantidad_disponible, 0) AS cantidad
        FROM talla t
        LEFT JOIN inventario i 
            ON t.id_talla = i.id_talla 
            AND i.id_modelo = :modelo
            AND i.id_sucursal = :sucursal
        WHERE t.id_modelo = :modelo
        ORDER BY t.numero_talla ASC";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    ":modelo" => $modelo,
    ":sucursal" => $sucursal
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);