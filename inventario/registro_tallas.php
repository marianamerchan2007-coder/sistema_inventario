<?php
session_start();
include("../config/conexion.php");
include("../includes/auth.php");
include('../includes/header.php');
include('../includes/sidebar.php'); 
?>

<?php
$id = $_GET['id'] ?? null;
$id_sucursal = $_GET['sucursal'] ?? null;
?>
<?php

$sql = "SELECT 
            t.id_talla,
            m.nombre_modelo,
            m.imagen,
            t.numero_talla,
            i.cantidad_disponible,
            i.fecha_ingreso,
            s.nombre_sucursal,
            CONCAT(m.nombre_modelo, '-', s.nombre_sucursal) AS codigo_qr
        FROM inventario i
        INNER JOIN talla t ON i.id_talla = t.id_talla
        INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
        INNER JOIN modelo m ON i.id_modelo = m.id_modelo
        WHERE i.id_modelo = :id
        AND i.id_sucursal = :sucursal
        ORDER BY t.numero_talla ASC";
        
$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":id" => $id,
    ":sucursal" => $id_sucursal
]);

$registros = $stmt ->fetchAll(PDO::FETCH_ASSOC);

$ultimos = [];

foreach ($registros as $registro) {

    $idTalla = $registro['id_talla'];

    // Si no existe o este registro es más nuevo, lo reemplaza
    if (!isset($ultimos[$idTalla])) {
        $ultimos[$idTalla] = $registro;
    }
}
?>

    <div class="main">
        <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h5 class="mb-2 mb-md-0">Detalle de tallas</h5>

                    <div class="d-flex gap-2">
                        <a href="index.php" class="btn btn-primary btn-sm"><i class="bi bi-arrow-left"></i></a>
                    </div>
                </div>

                <div class="modelo-card mb-4">

                    <div class="modelo-image">
                        <img src="<?= htmlspecialchars($registros[0]['imagen']); ?>" 
                            alt="image">
                    </div>

                    <div class="modelo-info">
                        <h4>
                            <?= htmlspecialchars($registros[0]['nombre_modelo']); ?>
                        </h4>

                        <p>
                            Sucursal: <?= htmlspecialchars($registros[0]['nombre_sucursal']); ?>
                        </p>
                    </div>

                </div>

                <div class="table-responsive d-none d-md-block">
                    <table class="table table-bordered table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Talla</th>
                                <th>Cantidad disponible</th>
                                <th>Último registro</th>
                            </tr>
                        </thead>

                        <tbody>
                           <?php foreach ($ultimos as $registro): ?>
                                <tr>
                                    <td><?= $registro['numero_talla']; ?></td>
                                    <td><span><?= $registro['cantidad_disponible']; ?></span></td>
                                    <td><?= date("d/m/Y h:i A", strtotime($registro['fecha_ingreso'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ===== MOBILE ===== -->
                <div class="d-md-none">
                    <?php foreach ($ultimos as $registro): ?>
                        <div class="talla-card-mobile">

                            <div class="talla-numero">
                                <?= $registro['numero_talla']; ?>
                            </div>

                            <div class="talla-info">
                                <div>
                                    <strong>Cantidad:</strong>
                                    <?= $registro['cantidad_disponible']; ?>
                                </div>

                                <small class="text-muted">
                                    <?= date("d/m/Y h:i A", strtotime($registro['fecha_ingreso'])) ?>
                                </small>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
        </div>
    </div>
    
<?php include('../includes/footer.php'); ?>

<style>
/* ===== BADGE TALLA ===== */
.talla-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    background: #eff6ff;
    color: #1f5eb1;
    font-weight: 600;
    font-size: 13px;
}

/* ===== MOBILE CARD ===== */
.talla-card-mobile {
    display: flex;
    justify-content: space-between;
    align-items: center;

    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;

    padding: 12px 14px;
    margin-bottom: 10px;

    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

/* número talla */
.talla-numero {
    width: 45px;
    height: 45px;
    border-radius: 10px;

    background: #1f5eb1;
    color: white;

    display: flex;
    align-items: center;
    justify-content: center;

    font-weight: 600;
}

/* info */
.talla-info {
    flex: 1;
    margin-left: 12px;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
</style>
