<?php include('../config/conexion.php'); ?>
<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID no enviado");
}
$id = $_GET['id'];


$sql = "SELECT 
    t.id_transferencia,
    t.fecha_transferencia,
    talla.numero_talla,
    t.estado,
    m.nombre_modelo,
    m.imagen,
    d.cantidad,
    d.stock_origen_antes,
    d.stock_origen_despues,
    d.stock_destino_antes,
    d.stock_destino_despues
FROM transferencias t
LEFT JOIN transferencia_detalle d 
    ON d.id_transferencia = t.id_transferencia
LEFT JOIN inventario i 
    ON i.id_inventario = t.id_inventario_origen
LEFT JOIN talla 
    ON talla.id_talla = d.id_talla
LEFT JOIN modelo m 
    ON m.id_modelo = i.id_modelo
WHERE t.id_transferencia = ?;";

$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
$registros=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="main">
        <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-4">
            <h5 class="mb-0">Detalle de transferencias</h5>
            <a href="index.php" class="btn btn-primary"><i class="bi bi-arrow-left"></i></a>
        </div>

        <!-- TABLA DE DETALLE -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Talla</th>
                        <th>Cantidad</th>
                        <th>Stock Origen</th>
                        <th>Stock Destino</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= date("d/m/Y h:i A", strtotime($r['fecha_transferencia'])); ?></td>
                            <td><?= $r['numero_talla']; ?></td>
                            <td><?= $r['cantidad']; ?></td>
                            <td>
                                <?= $r['stock_origen_antes'] ?> 
                                → 
                                <?= $r['stock_origen_despues'] ?>
                            </td>
                            <td>
                                <?= $r['stock_destino_antes'] ?> 
                                → 
                                <?= $r['stock_destino_despues'] ?>
                            </td>
                            <td><?= $r['estado']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ===== MOBILE (CARDS TRANSFERENCIA) ===== -->
        <div class="d-md-none">
            <?php foreach ($registros as $r): ?>
                
                <div class="venta-card">

                    <!-- HEADER -->
                    <div class="venta-header">
                        <h6>Talla <?= $r['numero_talla']; ?></h6>

                        <span class="badge venta-cantidad">
                            <?= $r['cantidad']; ?>
                        </span>
                    </div>

                    <!-- DETALLES -->
                    <div class="venta-info">

                        <span>
                            Origen: 
                            <?= $r['stock_origen_antes']; ?> → <?= $r['stock_origen_despues']; ?>
                        </span>

                        <span>
                            Destino: 
                            <?= $r['stock_destino_antes']; ?> → <?= $r['stock_destino_despues']; ?>
                        </span>

                        <span>
                            Estado: <?= $r['estado']; ?>
                        </span>

                        <span>
                            <i class="bi bi-calendar"></i>
                            <?= date("d/m/Y h:i A", strtotime($r['fecha_transferencia'])); ?>
                        </span>
                        
                    </div>

                </div>

            <?php endforeach; ?>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>


<style>
.venta-info .badge {
    margin-left: 6px;
}
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