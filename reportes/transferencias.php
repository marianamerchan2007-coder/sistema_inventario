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
$modelo = $_GET['modelo'] ?? null;


//lista modelos
$sql_modelos = "SELECT id_modelo, nombre_modelo FROM modelo";
$modelos =$conexion->query($sql_modelos)->fetchAll(PDO::FETCH_ASSOC);

//consulta principal: traer datos

$sql = "SELECT 
    m.nombre_modelo,
    t.numero_talla,
    dt.cantidad,
    so.nombre_sucursal AS sucursal_origen,
    sd.nombre_sucursal AS sucursal_destino,
    m.origen_producto,
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
WHERE 1=1
";

$params = [];

// Filtros
//solo se ejecuta si el usuario selecciona fechas
if($inicio && $fin){
    $sql .= " AND DATE(tr.fecha_transferencia) BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio;
    $params[':fin'] = $fin;
}

if($modelo){
    $sql .= " AND m.id_modelo = :modelo";
    $params[':modelo'] = $modelo;
}

$sql .= " ORDER BY tr.fecha_transferencia DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="main">
        <div class="container-fluid">
            <h5 class="mb-3">Reporte de transferencias Planta → Local</h5>

            <form method="GET" class="row">
                <div class="col-md-3 mt-3">
                    <label for="inicio">Fecha inicio</label>
                    <input type="date" name="inicio" id="inicio" class="form-control">
                </div>

                <div class="col-md-3 mt-3">
                    <label for="fin">Fecha fin</label>
                    <input type="date" name="fin" id="fin" class="form-control">
                </div>

                <div class="col-md-3 mt-3">
                    <label for="modelo">Modelo</label>
                    <select name="modelo" id="modelo" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach($modelos as $m): ?>
                            <option value="<?= $m['id_modelo'] ?>">
                                <?= $m['nombre_modelo'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2 mt-2">
                    <button class="btn btn-primary btn-filtro">Filtrar</button>
                    <a href="transferencias.php" class="btn btn-secondary btn-limpiar">Limpiar</a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-5">

                <a href="transferencias_pdf.php?inicio=<?= $inicio ?>&fin=<?= $fin ?>&modelo=<?= $modelo ?>"
                    class="btn btn-danger btn-sm d-flex align-items-center gap-1">
                    Descargar PDF
                </a>

            </div>

            <?php 
            $filtrosActivos = $inicio || $fin || $modelo;
            ?>

            <?php if (empty($registros)): ?>

                <div class="alert alert-warning mt-3">
                    <i class="bi bi-search"></i>

                    <?php if ($filtrosActivos): ?>
                        No se encontraron transferencias con los filtros aplicados
                    <?php else: ?>
                        No hay registros de transferencias
                    <?php endif; ?>
                </div>

            <?php endif; ?>
            <div class="table-responsive mt-3 d-none d-md-block">
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Modelo</th>
                            <th>Talla</th>
                            <th>Cantidad</th>
                            <th>Fecha transferencia</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($registros as $r): ?>
                            <tr>
                                <td><?= $r['nombre_modelo'] ?></td>
                                <td><?= $r['numero_talla'] ?></td>
                                <td><?= $r['cantidad'] ?></td>
                                <td><?= date("d/m/Y", strtotime($r['fecha_transferencia'])) ?></td>
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
                                <?= $r['cantidad'] ?>
                            </span>
                        </div>

                        <!-- INFO -->
                        <div class="reporte-info">
                            <span><strong>Talla:</strong> <?= $r['numero_talla'] ?></span>
                            <span><?= date("d/m/Y", strtotime($r['fecha_transferencia'])) ?></span>
                        </div>

                    </div>

                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>

<style>
.search-box {
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    width: 350px;
}

.search-box input {
    border: none;
    outline: none;
    box-shadow: none;
    padding: 6px 10px;
}

.search-box button {
    border-radius: 0;
    padding: 6px 12px;
}
</style>