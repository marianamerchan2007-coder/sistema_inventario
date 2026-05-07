<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;

include('../config/conexion.php');
include('../includes/auth.php');

// FILTROS
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;
$modelo = $_GET['modelo'] ?? null;

//  QUERY BASE
$sql = "SELECT 
            m.nombre_modelo,
            t.numero_talla,
            dt.cantidad,
            so.nombre_sucursal AS sucursal_origen,
            sd.nombre_sucursal AS sucursal_destino,
            tr.fecha_transferencia
        FROM transferencias tr
        INNER JOIN transferencia_detalle dt 
            ON tr.id_transferencia = dt.id_transferencia
        INNER JOIN inventario i 
            ON tr.id_inventario_origen = i.id_inventario
        INNER JOIN modelo m 
            ON i.id_modelo = m.id_modelo
        INNER JOIN talla t 
            ON dt.id_talla = t.id_talla
        INNER JOIN sucursal so 
            ON i.id_sucursal = so.id_sucursal
        INNER JOIN sucursal sd 
            ON tr.id_sucursal_destino = sd.id_sucursal
        WHERE 1=1";

$params = [];

//  FILTRO FECHAS
if ($inicio && $fin) {
    $sql .= " AND DATE(tr.fecha_transferencia) BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio;
    $params[':fin'] = $fin;
}

//  FILTRO MODELO (IMPORTANTE)
if ($modelo) {
    $sql .= " AND m.id_modelo = :modelo";
    $params[':modelo'] = $modelo;
}

$sql .= " ORDER BY tr.fecha_transferencia DESC";

//  EJECUTAR CONSULTA
$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

//  OBTENER NOMBRE DEL MODELO PARA MOSTRAR EN FILTROS
$nombre_modelo = null;
if ($modelo && count($data) > 0) {
    $nombre_modelo = $data[0]['nombre_modelo'];
}

//  HTML PDF
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
    font-size: 11px;
    text-align: right;
}
.filtros {
    font-size: 10px;
    margin-bottom: 10px;
}
</style>

<div class="header">
    <div class="title">Calzado Bernal</div>
    <div class="subtitle">Reporte de Transferencias</div>
    <div class="subtitle">'.date("d/m/Y H:i").'</div>
</div>

<div class="filtros">
';

if ($inicio && $fin) {
    $html .= "Fecha: $inicio a $fin<br>";
}

if ($nombre_modelo) {
    $html .= "Modelo: $nombre_modelo<br>";
}

$html .= '</div>';

$html .= '
<table>
<tr>
    <th>Modelo</th>
    <th>Talla</th>
    <th>Cantidad</th>
    <th>Origen</th>
    <th>Destino</th>
    <th>Fecha</th>
</tr>
';

$total = 0;

foreach ($data as $r) {
    $total += $r['cantidad'];

    $fecha = $r['fecha_transferencia']
        ? date("d/m/Y", strtotime($r['fecha_transferencia']))
        : '';

    $html .= "<tr>
        <td>{$r['nombre_modelo']}</td>
        <td>{$r['numero_talla']}</td>
        <td>{$r['cantidad']}</td>
        <td>{$r['sucursal_origen']}</td>
        <td>{$r['sucursal_destino']}</td>
        <td>{$fecha}</td>
    </tr>";
}

$html .= "</table>";

$html .= "
<div class='footer'>
    Total de unidades transferidas: <strong>$total</strong>
</div>
";

//  GENERAR PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();

//IMPRIMIR
$dompdf->stream("reporte_transferencias.pdf", ["Attachment" => true]);