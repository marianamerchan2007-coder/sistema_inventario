<?php
session_start();
include('../config/conexion.php');
include('../includes/auth.php');

if ($_SESSION['rol'] != 1) {
    header("Location: ../inventario/index.php");
    exit;
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<?php  
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin']?? null;
$id_sucursal = $_GET['sucursal'] ?? null;
$buscar = $_GET['buscar'] ?? null;
$tipo = $_GET['tipo'] ?? null;

//lista de nombre tipo de venta
$sql_tipo = "SELECT id_tipo_venta, nombre_tipo_venta FROM tipo_venta";
$tipos = $conexion->query($sql_tipo)->fetchAll(PDO::FETCH_ASSOC);

//lista sucursal
$sql_sucursal = "SELECT id_sucursal, nombre_sucursal FROM sucursal";
$sucursales = $conexion->query($sql_sucursal)->fetchAll(PDO::FETCH_ASSOC);


//consulta principal: traer datos

$sql = "SELECT v.fecha_venta, m.nombre_modelo, t.numero_talla, v.cantidad_vendida, 
tipo.nombre_tipo_venta, s.nombre_sucursal FROM ventas v
INNER JOIN inventario i ON v.id_inventario = i.id_inventario
INNER JOIN talla t ON i.id_talla = t.id_talla
INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
INNER JOIN modelo m ON i.id_modelo = m.id_modelo
INNER JOIN tipo_venta tipo ON v.id_tipo_venta = tipo.id_tipo_venta
WHERE 1=1";

$params = [];

// Filtros
//solo se ejecuta si el usuario selecciona fechas
if($inicio && $fin){
    $sql .= " AND DATE(v.fecha_venta) BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio;
    $params[':fin'] = $fin;
}

//filtro sucursal
if($id_sucursal) {
    $sql .= " AND i.id_sucursal = :sucursal";
    $params[':sucursal'] = $id_sucursal;
}

if($tipo){
    $sql .= " AND v.id_tipo_venta = :tipo";
    $params[':tipo'] = $tipo;
}

//buscador
if ($buscar) {
    $sql .= " AND (m.nombre_modelo LIKE :buscar OR i.codigo_qr LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

$sql .= " ORDER BY v.fecha_venta DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


    <div class="main">
        <div class="container-fluid">
            <h5 class="mb-3">Reporte de ventas</h5>

            <form method="GET" class="row">
                <div class="col-md-3 mt-3">
                    <label for="inicio">Fecha inicio</label>
                    <input type="date" name="inicio" id="inicio" class="form-control">
                </div>

                <div class="col-md-3 mt-3">
                    <label for="fin">Fecha fin</label>
                    <input type="date" name="fin" id="fin" class="form-control">
                </div>

                <div class="col-md-2 mt-3">
                    <label>Tipo de venta</label>
                    <select name="tipo" class="form-select">
                        <option value="">Todos</option>

                        <?php foreach($tipos as $t): ?>
                            <option value="<?= $t['id_tipo_venta'] ?>"
                                <?= ($tipo == $t['id_tipo_venta']) ? 'selected' : '' ?>>
                                <?= $t['nombre_tipo_venta'] ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-md-2 mt-3">
                    <label for="sucursal">Sucursal</label>
                    <select name="sucursal" id="sucursal" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach($sucursales as $s): ?>
                            <option value="<?= $s['id_sucursal'] ?>">
                                <?= $s['nombre_sucursal'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2 mt-2">
                    <button class="btn btn-primary btn-filtro">Filtrar</button>
                    <a href="ventas.php" class="btn btn-secondary btn-limpiar">Limpiar</a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-5">

                <a href="ventas_pdf.php?inicio=<?= $inicio ?>&fin=<?= $fin ?>&sucursal=<?= $id_sucursal ?>&buscar=<?= $buscar ?>&tipo=<?= $tipo ?>"
                    class="btn btn-danger btn-sm d-flex align-items-center gap-1">
                    Descargar PDF
                </a>

                <!-- BUSCADOR -->
                <form method="GET" class="search-box d-flex">
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="buscar" class="form-control form-control-sm"
                        placeholder="Buscar por modelo o código">
                </form>
            </div>

            <?php 
            $filtrosActivos = $inicio || $fin || $id_sucursal || $buscar || $tipo;
            ?>

            <?php if (empty($registros)): ?>

                <div class="alert alert-warning mt-3">
                    <?php if ($filtrosActivos): ?>
                        No se encontraron ventas con los filtros aplicados 
                    <?php else: ?>
                        No hay registros de ventas
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="table-responsive mt-3 d-none d-md-block">
                    <table class="table table-bordered table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Modelo</th>
                                <th>Talla</th>
                                <th>Cantidad</th>
                                <th>Sucursal</th>
                                <th>Fecha venta</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach($registros as $r): ?>
                                <tr>
                                    <td><?= $r['nombre_modelo'] ?></td>
                                    <td><?= $r['numero_talla'] ?></td>
                                    <td><?= $r['cantidad_vendida'] ?></td>
                                    <td><?= $r['nombre_sucursal'] ?></td>
                                    <td><?= date("d/m/Y", strtotime($r['fecha_venta'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-md-none mt-3">
                    <?php foreach($registros as $r): ?>

                        <div class="reporte-card">

                            <!-- HEADER -->
                            <div class="reporte-header">
                                <h6><?= $r['nombre_modelo'] ?></h6>

                                <span class="reporte-cantidad">
                                    <?= $r['cantidad_vendida'] ?>
                                </span>
                            </div>

                            <!-- INFO -->
                            <div class="reporte-info">
                                <span><strong>Talla:</strong> <?= $r['numero_talla'] ?></span>
                                <span><?= $r['nombre_sucursal'] ?></span>
                                <span><?= date("d/m/Y", strtotime($r['fecha_venta'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>