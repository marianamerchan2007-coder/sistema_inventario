<?php
session_start();
include("../includes/auth.php");
include ("../config/conexion.php");
include('../includes/header.php');
include('../includes/sidebar.php');

$id_rol = $_SESSION['rol'];
$id_sucursal_usuario = $_SESSION['sucursal'];
?>

<?php

//sucursal
$sql_suc = "SELECT id_sucursal, nombre_sucursal FROM sucursal";
$sucursales = $conexion->query($sql_suc)->fetchAll(PDO::FETCH_ASSOC);

// Jefe puede filtrar
if ($id_rol == 1) {
    $sucursal = $_GET['sucursal'] ?? null;
} 
// Operario queda fijo en su sucursal
else {
    $sucursal = $id_sucursal_usuario;
}

//ventas hoy
$sql_Ventas ="SELECT SUM(v.cantidad_vendida) AS total_vendido, COUNT(*) AS numero_ventas
FROM ventas v
INNER JOIN inventario i ON v.id_inventario = i.id_inventario
WHERE DATE(v.fecha_venta) = CURDATE()";

$params = [];

if($sucursal){
    $sql_Ventas .= " AND i.id_sucursal = :sucursal";
    $params[':sucursal'] = $sucursal;
}

$stmt = $conexion->prepare($sql_Ventas);
$stmt->execute($params);
$ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC);

//ventas por modelo
$sql_Modelos = "SELECT m.nombre_modelo, SUM(v.cantidad_vendida) AS total FROM ventas v
INNER JOIN inventario i ON v.id_inventario = i.id_inventario
INNER JOIN modelo m ON i.id_modelo = m.id_modelo
WHERE DATE(v.fecha_venta) = CURDATE()";


if($sucursal){
    $sql_Modelos .= " AND i.id_sucursal = :sucursal";
}

$sql_Modelos .= " GROUP BY m.id_modelo
ORDER BY total DESC";

$stmt = $conexion->prepare($sql_Modelos);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sql_bajo= "SELECT m.nombre_modelo, t.numero_talla, s.nombre_sucursal, i.cantidad_disponible FROM inventario i
INNER JOIN modelo m ON i.id_modelo = m.id_modelo
INNER JOIN talla t ON i.id_talla = t.id_talla
INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
WHERE i.cantidad_disponible <= 10";

if($sucursal){
    $sql_bajo .= " AND i.id_sucursal = :sucursal";
}

$sql_bajo .= " ORDER BY i.cantidad_disponible ASC";

$stmt = $conexion ->prepare($sql_bajo);
$stmt->execute($params);
$stockBajo = $stmt->fetchAll(PDO::FETCH_ASSOC);


//ventas ultimos 7 días
$sql_7dias = "SELECT DATE(fecha_venta) AS fecha,
SUM(cantidad_vendida) AS total
FROM ventas
WHERE fecha_venta >= CURDATE() - INTERVAL 6 DAY";

$params_7 = [];

if($sucursal){
    $sql_7dias .= " AND id_inventario IN (
        SELECT id_inventario FROM inventario WHERE id_sucursal = :sucursal
    )";
    $params_7[':sucursal'] = $sucursal;
}

$sql_7dias .= " GROUP BY DATE(fecha_venta)
ORDER BY fecha ASC";

$stmt = $conexion->prepare($sql_7dias);
$stmt->execute($params_7);
$data7 = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fechas = [];
$totales7 = [];

// generar últimos 7 días
for ($i = 6; $i >= 0; $i--) {
    $fecha = date("Y-m-d", strtotime("-$i days"));
    $fechas[$fecha] = 0;
}

// llenar con datos reales
foreach ($data7 as $d) {
    $fechas[$d['fecha']] = $d['total'];
}

// separar para chart
$labels7 = array_map(function($f){
    return date("d/m", strtotime($f));
}, array_keys($fechas));

$totales7 = array_values($fechas);


//Modelo mas vendido
$sql_top= "SELECT m.nombre_modelo, SUM(v.cantidad_vendida) AS total FROM ventas v
INNER JOIN inventario i ON v.id_inventario = i.id_inventario
INNER JOIN modelo m ON i.id_modelo = m.id_modelo
WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";

if($sucursal){
    $sql_top .= " AND i.id_sucursal = :sucursal";
}

$sql_top .= " GROUP BY m.id_modelo
ORDER BY total DESC
LIMIT 1";

$stmt = $conexion->prepare($sql_top);
$stmt->execute($params);
$topModelo = $stmt->fetch(PDO::FETCH_ASSOC);

//Mayorista o minorista
$sql_tipo7 = "
SELECT 
    DATE(v.fecha_venta) AS fecha,
    tv.nombre_tipo_venta,
    SUM(v.cantidad_vendida) AS total
FROM ventas v
INNER JOIN tipo_venta tv ON v.id_tipo_venta = tv.id_tipo_venta
WHERE v.fecha_venta >= CURDATE() - INTERVAL 6 DAY";

if ($sucursal) {
    $sql_tipo7 .= " AND v.id_inventario IN (
        SELECT id_inventario FROM inventario WHERE id_sucursal = :sucursal
    )";
}

$sql_tipo7 .= "
GROUP BY DATE(v.fecha_venta), tv.nombre_tipo_venta
ORDER BY fecha ASC
";

$stmt = $conexion->prepare($sql_tipo7);
$stmt->execute($params_7 ?? []);
$dataTipo7 = $stmt->fetchAll(PDO::FETCH_ASSOC);


$labelsTipo = [];
$mayorista = [];
$minorista = [];

// inicializar últimos 7 días
$fechas = [];

for ($i = 6; $i >= 0; $i--) {
    $fecha = date("Y-m-d", strtotime("-$i days"));

    $fechas[$fecha] = [
        'Mayorista' => 0,
        'Minorista' => 0
    ];
}

// llenar datos reales
foreach ($dataTipo7 as $d) {
    $tipo = $d['nombre_tipo_venta'];
    $fecha = $d['fecha'];

    if (isset($fechas[$fecha][$tipo])) {
        $fechas[$fecha][$tipo] = $d['total'];
    }
}

// separar para gráfico
foreach ($fechas as $fecha => $valores) {

    $labelsTipo[] = date("d/m", strtotime($fecha));

    $mayorista[] = $valores['Mayorista'] ?? 0;
    $minorista[] = $valores['Minorista'] ?? 0;
}
?>

<?php
$labels = [];
$totales = [];

foreach ($data as $r) {
    $labels[] = $r['nombre_modelo'];
    $totales[] = $r['total'];
}
?>

<div class="main">
    <div class="container-fluid dashboard-container">
        <div class="row">
            <?php if ($id_rol == 1): ?>
                <form method="GET" class="filter-box mb-2 ">
                    <select name="sucursal" class="form-select filter-select" onchange="this.form.submit()">
                        <option value="">Todas las sucursales</option>
                        <?php foreach($sucursales as $s): ?>
                            <option value="<?= $s['id_sucursal'] ?>" 
                                <?= ($sucursal == $s['id_sucursal']) ? 'selected' : '' ?>>
                                <?= $s['nombre_sucursal'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="row">
            <div class="col-md-3 col-sm-6 col-6 mb-2">
                <div class="card card-kpi shadow-sm border-0 p-2">
                    <div class="card-body text-center">
                        <h6 class="text-muted fw-bold">Ventas del día</h6>
                        <h2 class="fw-bold">
                            <?= $ventasHoy['total_vendido'] ?? 0 ?>
                        </h2>
                        <small class="text-muted">pares</small><br>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-6 mb-3">
                <div class="card card-kpi shadow-sm border-0 p-2">
                    <div class="card-body text-center">
                        <h6 class="text-muted fw-bold">Producto más vendido</h6>
                        <h2 class="fw-bold">
                            <?= $topModelo['nombre_modelo'] ?? 'Sin ventas' ?>
                        </h2>
                        <small class="text-muted"><?= $topModelo['total'] ?? 0 ?> unidades</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-6 mb-2">
                <div class="card card-kpi shadow-sm border-0 p-2">
                    <div class="card-body text-center">
                        <h6 class="text-muted fw-bold">Número de ventas hoy</h6>
                        <h2 class="fw-bold" style="font-size:35px;">
                            <?= $ventasHoy['numero_ventas'] ?? 0 ?>
                        </h2>
                        
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 col-6 mb-3">
                <div class="card card-kpi shadow-sm border-0 p-2">
                    <div class="card-body text-center">
                        <h6 class="text-muted fw-bold">Productos stock bajo</h6>
                        <h2 class="fw-bold text-danger" style="font-size:35px;">
                            <?= count($stockBajo) ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="chart-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted">Ventas del día por modelo</h6>
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 ">
                <div class=" chart-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted">Ventas últimos 7 días</h6>
                        <canvas id="chart7dias"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class=" chart-card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted">Ventas por tipo de venta</h6>
                        <canvas id="ventasTipo7"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12 mb-2">
                <h5 class="section-title">⚠️ Modelos con stock bajo</h5>
                <?php if(empty($stockBajo)): ?>
                    <div class="col-md-6">
                        <div class="alert alert-primary shadow-sm">
                            ✅ Todo el inventario está en buen nivel
                        </div>
                    </div>
                <?php else: ?>
            </div>
        </div>

        <div class="row">
            <?php foreach($stockBajo as $item): 
                $color = $item['cantidad_disponible'] <= 5 ? 'danger' : 'warning'; ?>

                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card stock-card mb-3">
                        <div class="top-line bg-<?= $color ?>"></div>

                        <div class="card-body">
                            <h6 class="mb-1" style="font-size:16px;">
                                <?= $item['nombre_modelo'] ?>
                            </h6>

                            <div class="info">
                                <span> Talla <?= $item['numero_talla'] ?></span>
                                <span>  |  <?= $item['nombre_sucursal'] ?></span>
                            </div>

                            <div class="stock mt-3 text-<?= $color ?>"> Stock:
                                <?= $item['cantidad_disponible'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('ventasChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Ventas',
            data: <?= json_encode($totales) ?>,
            backgroundColor: '#4f46e5',
            borderRadius: 0,
            barThickness: 60
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: {
                    font: { size: 11 }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                },
                ticks: {
                    font: { size: 11 }
                }
            }
        }
    }
});


const ctx7 = document.getElementById('chart7dias');

new Chart(ctx7, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels7) ?>,
        datasets: [{
            label: 'Ventas',
            data: <?= json_encode($totales7) ?>,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(118, 163, 232, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#164a93'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { grid: { display: false } },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' }
            }
        }
    }
});



const ctxTipo = document.getElementById('ventasTipo7');

new Chart(ctxTipo, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelsTipo) ?>,
        datasets: [
            {
                label: 'Mayorista',
                data: <?= json_encode($mayorista) ?>,
                backgroundColor: '#4f46e5'
            },
            {
                label: 'Minorista',
                data: <?= json_encode($minorista) ?>,
                backgroundColor: '#20fd14'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top',
                labels: {
                        font: { size: 11 }
                }
            }

        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)'}
             }
        }
    }
});
</script>

