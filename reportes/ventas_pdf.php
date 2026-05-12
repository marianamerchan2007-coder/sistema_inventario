<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;

include('../config/conexion.php');
include('../includes/auth.php');

//  FILTROS
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;
$id_sucursal = $_GET['sucursal'] ?? null;
$buscar = $_GET['buscar'] ?? null;
$tipo = $_GET['tipo'] ?? null;

//  QUERY
$sql = "SELECT 
            v.fecha_venta,
            m.nombre_modelo,
            t.numero_talla,
            v.cantidad_vendida,
            tipo.nombre_tipo_venta,
            s.nombre_sucursal
        FROM ventas v
        INNER JOIN inventario i ON v.id_inventario = i.id_inventario
        INNER JOIN modelo m ON i.id_modelo = m.id_modelo
        INNER JOIN talla t ON i.id_talla = t.id_talla
        INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
        INNER JOIN tipo_venta tipo ON v.id_tipo_venta = tipo.id_tipo_venta
        WHERE 1=1";

$params = [];

$nombreTipo = '';

if ($tipo) {
    $stmtTipo = $conexion->prepare("SELECT nombre_tipo_venta FROM tipo_venta WHERE id_tipo_venta = ?");
    $stmtTipo->execute([$tipo]);
    $nombreTipo = $stmtTipo->fetchColumn();
}

// 🔹 FILTRO FECHA
if ($inicio && $fin) {
    $sql .= " AND DATE(v.fecha_venta) BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio;
    $params[':fin'] = $fin;
}

// 🔹 FILTRO SUCURSAL
$nombreSucursal = '';

if ($id_sucursal) {
    $stmtSuc = $conexion->prepare("SELECT nombre_sucursal FROM sucursal WHERE id_sucursal = ?");
    $stmtSuc->execute([$id_sucursal]);
    $nombreSucursal = $stmtSuc->fetchColumn();
}

if($tipo){
    $sql .= " AND v.id_tipo_venta = :tipo";
    $params[':tipo'] = $tipo;
}


// 🔹 BUSCADOR
if ($buscar) {
    $sql .= " AND (m.nombre_modelo LIKE :buscar OR i.codigo_qr LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

$sql .= " ORDER BY v.fecha_venta DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

//  HTML DEL PDF
$html = '
<style>
body {
    font-family: Arial, sans-serif;
    font-size: 11px;
}
.header {
    text-align: center;
    margin-bottom: 15px;
}
.title {
    font-size: 18px;
    font-weight: bold;
}
.subtitle {
    font-size: 12px;
    color: gray;
}
table {
    border-collapse: collapse;
    width: 100%;
}
th {
    background: #3154ad;
    color: white;
}
th, td {
    border: 1px solid #ddd;
    padding: 6px;
    text-align: center;
}
tr:nth-child(even) {
    background: #f2f2f2;
}
.footer {
    margin-top: 10px;
    font-size: 10px;
    text-align: right;
}
.filtros {
    font-size: 10px;
    margin-bottom: 10px;
}
</style>

<div class="header">
    <div class="title">Calzado Bernal</div>
    <div class="subtitle">Reporte de Ventas</div>
    <div class="subtitle">'.date("d/m/Y h:i A").'</div>
</div>

<div class="filtros">
';

//  MOSTRAR FILTROS
if ($inicio && $fin) {
    $html .= "<strong>Fecha:</strong> " . date("d/m/Y", strtotime($inicio)) . 
              " a " . date("d/m/Y", strtotime($fin)) . "<br>";
}
if ($id_sucursal) {
    $html .= "Sucursal: $nombreSucursal<br>";
}
if ($buscar) {
    $html .= "Búsqueda: $buscar<br>";
}

if ($tipo) {
    $html .= "Tipo de venta: $nombreTipo<br>";
}


$html .= '</div>';

$html .= '
<table>
<tr>
    <th>Modelo</th>
    <th>Talla</th>
    <th>Cantidad</th>
    <th>Tipo Venta</th>
    <th>Sucursal</th>
    <th>Fecha</th>
</tr>
';

//  DATOS
foreach ($data as $r) {
    $html .= "<tr>
        <td>{$r['nombre_modelo']}</td>
        <td>{$r['numero_talla']}</td>
        <td>{$r['cantidad_vendida']}</td>
        <td>{$r['nombre_tipo_venta']}</td>
        <td>{$r['nombre_sucursal']}</td>
        <td>".date("d/m/Y", strtotime($r['fecha_venta']))."</td>
    </tr>";
}

$html .= "</table>";

//  TOTAL VENDIDO
$total = array_sum(array_column($data, 'cantidad_vendida'));

$html .= "
<div class='footer'>
    Total de unidades vendidas: <strong>$total</strong><br>
</div>
";

//  GENERAR PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();

//  IMPRIMIR
$dompdf->stream("reporte_ventas.pdf", ["Attachment" => true]);