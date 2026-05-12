<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;

include('../config/conexion.php');
include('../includes/auth.php');  

//  RECIBIR FILTROS
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;
$id_sucursal = $_GET['sucursal'] ?? null;
$buscar = $_GET['buscar'] ?? null;

//  QUERY BASE
$sql = "SELECT 
            m.nombre_modelo,
            t.numero_talla,
            m.origen_producto,
            i.cantidad_disponible,
            s.nombre_sucursal,
            i.fecha_ingreso
        FROM inventario i
        INNER JOIN modelo m ON i.id_modelo = m.id_modelo
        INNER JOIN talla t ON i.id_talla = t.id_talla
        INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
        WHERE 1=1";

$params = [];

//  FILTRO FECHA
if ($inicio && $fin) {
    $sql .= " AND DATE(i.fecha_ingreso) BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio;
    $params[':fin'] = $fin;
}

//  FILTRO SUCURSAL
if ($id_sucursal) {
    $sql .= " AND i.id_sucursal = :sucursal";
    $params[':sucursal'] = $id_sucursal;
}

//  BUSCADOR
if ($buscar) {
    $sql .= " AND (m.nombre_modelo LIKE :buscar OR i.codigo_qr LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

$sql .= " ORDER BY i.fecha_ingreso DESC";

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
    background:  #3154ad;
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
    <div class="subtitle">Reporte de Inventario</div>
    <div class="subtitle">'.date("d/m/Y H:i A").'</div>
</div>

<div class="filtros">
';

//  MOSTRAR FILTROS ACTIVOS
if ($inicio && $fin) {
    $html .= "Fecha: $inicio a $fin<br>";
}
if ($id_sucursal) {
    $html .= "Sucursal ID: $id_sucursal<br>";
}
if ($buscar) {
    $html .= "Búsqueda: $buscar<br>";
}

$html .= '</div>';

$html .= '
<table>
<tr>
    <th>Modelo</th>
    <th>Talla</th>
    <th>Cantidad</th>
    <th>Sucursal</th>
    <th>Fecha</th>
</tr>
';

//  DATOS
foreach ($data as $r) {
    $html .= "<tr>
        <td>{$r['nombre_modelo']}</td>
        <td>{$r['numero_talla']}</td>
        <td>{$r['cantidad_disponible']}</td>
        <td>{$r['nombre_sucursal']}</td>
        <td>".date("d/m/Y", strtotime($r['fecha_ingreso']))."</td>
    </tr>";
}

$html .= "</table>";

//  TOTAL
$total = array_sum(array_column($data, 'cantidad_disponible'));

$html .= "
<div class='footer'>
    Total de unidades: <strong>$total</strong><br>
</div>
";

//  GENERAR PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();

//IMPRIMIR
$dompdf->stream("reporte_inventario.pdf", ["Attachment" => true]);