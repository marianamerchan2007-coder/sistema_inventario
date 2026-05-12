<?php
session_start();
include("../includes/auth.php");

if ($_SESSION['sucursal'] != 2) {
    if ($_SESSION['rol'] == 1) {
        header("Location: ../dashboard/index.php");
    } 
    else {
        header("Location: ../inventario/index.php");
    }
    exit;
}

include("../config/conexion.php");
include("../includes/header.php");
include("../includes/sidebar.php");
?>

<?php 
$sql = "SELECT tr.id_transferencia, tr.fecha_transferencia, tr.estado, m.nombre_modelo, t.numero_talla, d.cantidad,
so.nombre_sucursal AS sucursal_origen, sd.nombre_sucursal AS sucursal_destino
FROM transferencias tr
INNER JOIN transferencia_detalle d 
    ON d.id_transferencia = tr.id_transferencia
INNER JOIN inventario io 
    ON tr.id_inventario_origen = io.id_inventario
INNER JOIN modelo m 
    ON io.id_modelo = m.id_modelo
INNER JOIN talla t 
    ON d.id_talla = t.id_talla
INNER JOIN sucursal so 
    ON io.id_sucursal = so.id_sucursal
INNER JOIN sucursal sd 
    ON tr.id_sucursal_destino = sd.id_sucursal
WHERE tr.estado = 'pendiente'
ORDER BY tr.id_transferencia DESC";


$stmt = $conexion->query($sql);
$registros = $stmt-> fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="main">
        <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center">
                    <h5> Confirmación de registros</h5>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_GET['msg']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_GET['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($registros)): ?>
    
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-check-circle"></i>
                        No hay transferencias pendientes
                    </div>

                <?php else: ?>
                    <div class="table-responsive mt-3 d-none d-md-block">
                        <table class="table align-middle custom-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Modelo</th>
                                    <th>Talla</th>
                                    <th>Cantidad</th>
                                    <th>Sucursal de origen</th>
                                    <th>Sucursal de destino</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($registros as $registro): ?>
                                <tr>
                                    <td><?php echo $registro['nombre_modelo'] ?></td>
                                    <td><?php echo $registro['numero_talla'] ?></td>
                                    <td><?php echo $registro['cantidad'] ?></td>
                                    <td><?php echo $registro['sucursal_origen'] ?></td>
                                    <td><?php echo $registro['sucursal_destino'] ?></td>
                                    <td>
                                    <?= date("d/m/Y h:i A", strtotime($registro['fecha_transferencia'])) ?>
                                    </td>

                                    <td>
                                        <?php if ($registro['estado'] == 'pendiente'): ?>
                                            <a href="transferencias_confirmar.php?id=<?= $registro['id_transferencia'] ?>"
                                            class="btn btn-confirmar">
                                                Confirmar
                                            </a>
                                            
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- ===== MOBILE (CARDS TRANSFERENCIAS) ===== -->
                <div class="d-md-none mt-3">
                    <?php foreach($registros as $registro): ?>
                        
                        <div class="transferencia-card">
                            <!-- HEADER -->
                            <div>
                                <h6><?= $registro['nombre_modelo']; ?></h6>

                                <!-- 🔥 CANTIDAD PROTAGONISTA -->
                                <span>
                                    <strong>Cantidad a transferir:</strong> <?=$registro['cantidad']; ?>
                                </span>
                            </div>

                            <!-- INFO -->
                            <div class="transferencia-info">
                                <span><strong>Talla:</strong> <?= $registro['numero_talla']; ?></span>
                                <span><?= $registro['sucursal_origen']; ?> → <?= $registro['sucursal_destino']; ?></span>
                                <span><?= date("d/m/Y h:i A", strtotime($registro['fecha_transferencia'])) ?></span>
                            </div>

                            <!-- ACCIÓN -->
                            <div class="transferencia-actions">
                                <a href="transferencias_confirmar.php?id=<?= $registro['id_transferencia'] ?>"
                                class="btn btn-confirmar btn-sm">
                                Confirmar
                                </a>
                            </div>

                        </div>

                    <?php endforeach; ?>
                </div>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>
<style>
.btn-confirmar {
    background: #16a34a;
    color: #fff !important;
    border: none;
    border-radius: 10px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

/* Hover */
.btn-confirmar:hover {
    background: #199546;
}

/* CARD */
.transferencia-card {
    background: #fff;
    border-radius: 14px;
    padding: 14px;
    margin-bottom: 12px;

    border: 1px solid #eef1f6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

/* INFO */
.transferencia-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
    color: #64748b;
}

/* ACCIONES */
.transferencia-actions {
    margin-top: 10px;
}

.transferencia-card h6 {
    margin-bottom: 2px;
}

.transferencia-card span {
    font-size: 13px;
    color: #475569;
}
</style>