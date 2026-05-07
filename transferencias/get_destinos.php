
<?php
include('../config/conexion.php');

$id_origen = $_POST['id_origen'];

// obtener modelo y talla del origen
$sql = "SELECT id_modelo, id_talla 
        FROM inventario 
        WHERE id_inventario = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_origen]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// buscar destinos compatibles
$sql = "SELECT i.id_inventario, s.nombre_sucursal
        FROM inventario i
        INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
        WHERE i.id_modelo = ?
        AND i.id_talla = ?
        AND i.id_inventario != ?";

$stmt = $conexion->prepare($sql);
$stmt->execute([
    $data['id_modelo'],
    $data['id_talla'],
    $id_origen
]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>