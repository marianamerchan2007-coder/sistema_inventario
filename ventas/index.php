<?php
session_start();
include("../config/conexion.php");
include("../includes/auth.php");

// datos del usuario logueado
$id_rol = $_SESSION['rol'];
$id_sucursal = $_SESSION['sucursal'];

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

unset($_SESSION['success'], $_SESSION['error']);

include('../includes/header.php'); 
include('../includes/sidebar.php'); 
?>


<?php
$buscar = $_GET['buscar'] ?? '';
$sql = "SELECT v.id_inventario, v.id_venta, v.cantidad_vendida, DATE_FORMAT(v.fecha_venta, '%d/%m/%Y') AS fecha_venta, tv.nombre_tipo_venta, m.nombre_modelo, t.numero_talla,
s.id_sucursal, s.nombre_sucursal, i.codigo_qr FROM ventas v
INNER JOIN inventario i ON v.id_inventario = i.id_inventario
INNER JOIN modelo m ON i.id_modelo = m.id_modelo
INNER JOIN talla t ON i.id_talla = t.id_talla
INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
INNER JOIN tipo_venta tv ON v.id_tipo_venta = tv.id_tipo_venta
WHERE 1=1";

if (!empty($buscar)) {
    $sql .= " AND (m.nombre_modelo LIKE :buscar OR s.nombre_sucursal LIKE :buscar)";
}

if ($id_rol != 1) {
    $sql .= " AND i.id_sucursal = :sucursal";
    $params[':sucursal'] = $id_sucursal;
}

$sql .= " ORDER BY v.id_venta DESC";

$stmt = $conexion->prepare($sql);

$params = [];

if (!empty($buscar)) {
    $params[':buscar'] = "%$buscar%";
}

if ($id_rol != 1) {
    $params[':sucursal'] = $id_sucursal;
}

$stmt->execute($params);

$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="main">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
                <h5 class="mb-2 mb-md-0">Lista de ventas
                    <?php if ($id_rol == 2 && !empty($ventas)): ?>
                        - <?= $ventas[0]['nombre_sucursal'] ?>
                    <?php endif; ?>
                </h5>
                
                 <!-- BUSCADOR -->
                <div class="d-flex flex-column flex-sm-row gap-2 ms-md-auto">
                    <form method="GET" class="search-box d-flex">
                        <button class="btn btn-primary btn-sm">
                            <i class="bi bi-search"></i>
                        </button>
                        <input type="text" name="buscar" class="form-control form-control-sm"
                            placeholder="Buscar por modelo o sucursal">
                    </form>

                    <a href="registrar_venta.php" class="btn btn-primary btn-nuevo">+ Nueva venta</a>
                </div>
            </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($buscar != '' && empty($ventas)): ?>
                    <div class="alert alert-warning">
                        No se encontraron registros de venta de 
                        <strong><?= htmlspecialchars($buscar) ?></strong>
                    </div>
                <?php endif; ?>


                <div class="table-responsive d-none d-md-block">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Modelo</th>
                                <th>Talla</th>
                                <th>Cantidad</th>
                                <?php if ($id_rol == 1): ?>
                                    <th>Sucursal</th>
                                <?php endif; ?>
                                <th>Tipo de venta</th>
                                <th>Fecha de venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?= $venta['nombre_modelo']; ?></td>
                                    <td><?= $venta['numero_talla']; ?></td>
                                    <td><?= $venta['cantidad_vendida']; ?></td>
                                    <?php if ($id_rol == 1): ?>
                                        <td><?= $venta['nombre_sucursal'] ?? ''; ?></td>
                                    <?php endif; ?>
                                    <td><?= $venta['nombre_tipo_venta']; ?></td>
                                    <td><?= $venta['fecha_venta']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ===== MOBILE (CARDS VENTAS) ===== -->
                <div class="d-md-none">
                    <?php foreach ($ventas as $venta): ?>
                        
                        <div class="venta-card">

                            <!-- HEADER -->
                            <div class="venta-header">
                                <h6><?= $venta['nombre_modelo']; ?></h6>
                                <span class="badge venta-cantidad">
                                    <?= $venta['cantidad_vendida']; ?>
                                </span>
                            </div>

                            <!-- DETALLES -->
                            <div class="venta-info">
                                <span> Talla: <?= $venta['numero_talla']; ?></span>
                                
                                <?php if ($id_rol == 1): ?>
                                    <span></i> <?= $venta['nombre_sucursal']; ?></span>
                                <?php endif; ?>

                                <span></i> <?= $venta['nombre_tipo_venta']; ?></span>
                                <span><i class="bi bi-calendar"></i> <?= $venta['fecha_venta']; ?></span>
                            </div>

                        </div>

                    <?php endforeach; ?>
                </div>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>

<style>
/* ===== CARDS VENTAS ===== */
.venta-card {
    background: #fff;
    border-radius: 14px;
    padding: 14px;
    margin-bottom: 12px;

    border: 1px solid #eef1f6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);

    transition: 0.2s;
}

.venta-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

/* HEADER */
.venta-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.venta-header h6 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

/* BADGE CANTIDAD */
.venta-cantidad {
    background: #eef2ff;
    color: #3730a3;
    font-size: 13px;
    padding: 5px 8px;
    border-radius: 8px;
}

/* INFO */
.venta-info {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
    color: #64748b;
}

.venta-info span {
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>