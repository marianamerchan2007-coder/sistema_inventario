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


//lista sucursal
$sql_sucursal = "SELECT id_sucursal, nombre_sucursal FROM sucursal";
$sucursales = $conexion->query($sql_sucursal)->fetchAll(PDO::FETCH_ASSOC);


//consulta principal: traer datos

$sql = "SELECT m.nombre_modelo, t.numero_talla, m.origen_producto, i.cantidad_disponible, i.codigo_qr,
s.nombre_sucursal, i.fecha_ingreso FROM inventario i 
INNER JOIN modelo m ON i.id_modelo = m.id_modelo
INNER JOIN talla t ON i.id_talla = t.id_talla
INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
WHERE 1=1";

$params = [];

// Filtros
//solo se ejecuta si el usuario selecciona fechas
if($inicio && $fin){
    $sql .= " AND DATE(i.fecha_ingreso) BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio;
    $params[':fin'] = $fin;
}

//filtro sucursal
if($id_sucursal) {
    $sql .= " AND i.id_sucursal = :sucursal";
    $params[':sucursal'] = $id_sucursal;
}

//buscador
if ($buscar) {
    $sql .= " AND (m.nombre_modelo LIKE :buscar OR i.codigo_qr LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

$sql .= " ORDER BY i.fecha_ingreso DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


    <div class="main">
        <div class="container-fluid">
            <h5 class="mb-3">Reporte de inventrario</h5>

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
                    <button class="btn btn-primary btn-sm btn-filtro">Filtrar</button>
                    <a href="inventario.php" class="btn btn-secondary btn-sm btn-limpiar">Limpiar</a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-5">
                <a href="inventario_pdf.php?inicio=<?= $inicio ?>&fin=<?= $fin ?>&sucursal=<?= $id_sucursal ?>&buscar=<?= $buscar  ?>"
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
            $filtrosActivos = $inicio || $fin || $id_sucursal || $buscar;
            ?>

            <?php if (empty($registros)): ?>

                <div class="alert alert-warning mt-3">
                    <i class="bi bi-search"></i>
                    
                    <?php if ($filtrosActivos): ?>
                       No se encontraron registros con los filtros aplicados
                    <?php else: ?>
                        No hay registros de inventario
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
                                <th>Fecha ingreso</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach($registros as $r): ?>
                                <tr>
                                    <td><?= $r['nombre_modelo'] ?></td>
                                    <td><?= $r['numero_talla'] ?></td>
                                    <td><?= $r['cantidad_disponible'] ?></td>
                                    <td><?= $r['nombre_sucursal'] ?></td>
                                    <td><?= date("d/m/Y", strtotime($r['fecha_ingreso'])) ?></td>
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
                                    <?= $r['cantidad_disponible'] ?>
                                </span>
                            </div>

                            <!-- INFO -->
                            <div class="reporte-info">
                                <span><strong>Talla:</strong> <?= $r['numero_talla'] ?></span>
                                <span><?= $r['nombre_sucursal'] ?></span>
                                <span><?= date("d/m/Y", strtotime($r['fecha_ingreso'])) ?></span>
                            </div>

                        </div>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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