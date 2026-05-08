<?php
session_start();
include('../config/conexion.php');


// ❌ Si NO es jefe → fuera
if ($_SESSION['rol'] != 1) {
    header("Location: ../inventario/index.php");
    exit;
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<?php
$sql = "SELECT 
    t.id_transferencia,
    m.nombre_modelo,
    m.imagen,
    COALESCE(SUM(d.cantidad),0) AS total,
    t.fecha_transferencia,
    t.estado
FROM transferencias t
INNER JOIN inventario i 
    ON t.id_inventario_origen = i.id_inventario
INNER JOIN modelo m 
    ON i.id_modelo = m.id_modelo
LEFT JOIN transferencia_detalle d 
    ON d.id_transferencia = t.id_transferencia
GROUP BY 
    t.id_transferencia,
    m.nombre_modelo,
    t.fecha_transferencia,
    t.estado
ORDER BY t.id_transferencia DESC;
";
        
$stmt=$conexion->query($sql);
$registros=$stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

    <div class="main">
        <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <h5 class="mb-2 mb-md-0">Lista de Transferencias - Planta a local</h5>
                    <a href="nueva_transferencia.php" class="btn btn-primary btn-nuevo">+ Nueva transferencia</a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Modelo</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($registros as $r): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $r['imagen']; ?>"
                                        width="100" height="80"
                                        style="object-fit: cover; border-radius: 10px;"
                                        onclick="verImagen(this)">
                                    </td>
                                    
                                    <td><?php echo $r['nombre_modelo']; ?></td>
                                    <td>
                                        <span class="badge bg-primary fs-7 px-3 py-2">
                                            <?php echo $r['total']?>
                                        </span>
                                    </td>
                                    
                                    <td><?= date("d/m/Y H:i", strtotime($r['fecha_transferencia'])) ?></td>
                                    
                                    <td>
                                        <?php if ($r['estado'] == 'pendiente'): ?>
                                            <span class="badge bg-pendiente">Pendiente</span>
                                        <?php elseif ($r['estado'] == 'confirmado'): ?>
                                            <span class="badge bg-confirmado">Confirmado</span>
                                        <?php else: ?>
                                            <span class="badge bg-otro"><?= $r['estado'] ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="detalle_transferencia.php?id=<?= $r['id_transferencia'] ?>" class="btn btn-editar btn-sm">
                                        Detalles</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ===== MOBILE TRANSFERENCIAS ===== -->
                <div class="d-md-none">

                    <?php foreach ($registros as $r): ?>

                    <div class="inventario-card">

                        <!-- IMAGEN -->
                        <div class="inventario-img-container">
                            <img src="<?php echo $r['imagen']; ?>" class="inventario-img">
                        </div>

                        <!-- CONTENIDO -->
                        <div class="inventario-content">

                            <!-- HEADER -->
                            <div class="inventario-header">

                                <div class="inventario-title">
                                    <h6><?= $r['nombre_modelo']; ?></h6>

                                    <span class="sucursal-chip">
                                        <?= date("d/m/Y", strtotime($r['fecha_transferencia'])) ?>
                                    </span>
                                </div>

                                <span class="badge stock-badge">
                                    <?= $r['total'] ?>
                                </span>

                            </div>

                            <!-- ESTADO -->
                            <div class="d-flex align-items-center gap-2">

                                <?php if ($r['estado'] == 'pendiente'): ?>
                                    <span class="badge bg-pendiente1">Pendiente</span>
                                <?php elseif ($r['estado'] == 'confirmado'): ?>
                                    <span class="badge bg-confirmado1">Confirmado</span>
                                <?php else: ?>
                                    <span class="badge bg-otro"><?= $r['estado'] ?></span>
                                <?php endif; ?>

                                <a href="detalle_transferencia.php?id=<?= $r['id_transferencia'] ?>" 
                                class="btn btn-tallas1">
                                    Ver detalles
                                </a>

                            </div>

                        </div>
                    </div>

                    <?php endforeach; ?>

                </div>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>

<style>
/* BADGES PERSONALIZADAS */
.bg-pendiente {
    background: #b1ee97;
    color: #06462e;
    border-radius: 20px;
    padding: 8px 10px;
    font-size: 13px;
    font-weight: 500;
}

.bg-confirmado {
    background: #a0c9f4;
    color: #272883;
    border-radius: 20px;
    padding: 8px 10px;
    font-size: 13px;
    font-weight: 500;
}

.bg-otro {
    background: #e9fca5;
    color: #41464b;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
}

.bg-pendiente1 {
    background: #b1ee97;
    color: #06462e;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
}

.bg-confirmado1 {
    background: #a0c9f4;
    color: #272883;
    border-radius: 20px;
    padding: 8px 10px;
    font-size: 11px;
    font-weight: 500;
}
.btn-editar {
    background: #1e5dcb;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    padding: 6px 12px;
    font-size: 13px;

    display: inline-flex;
    align-items: center;
    gap: 6px;

    transition: 0.2s;
}

.btn-editar:hover {
    background: #2161ba;
    color: #e9ebf1;
    transform: translateY(-1px);
}


/* ===== INVENTARIO MOBILE ===== */
/* CARD */
.inventario-card {
    display: flex;
    gap: 12px;
    background: #ffffff;
    padding: 14px;
    border-radius: 16px;
    margin-bottom: 14px;

    border: 1px solid #eef1f6;
    box-shadow: 0 6px 14px rgba(0,0,0,0.06);

    transition: all 0.2s ease;
}

.inventario-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
}

/* IMAGEN */
.inventario-img-container {
    width: 75px;
    height: 75px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.inventario-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* CONTENIDO */
.inventario-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* HEADER */
.inventario-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.inventario-header h6 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

/* STOCK */
.stock-badge {
    background: #eefff3;
    color: #4ec885;
    font-size: 12px;
    padding: 5px 8px;
    border-radius: 8px;
}

/* SUCURSAL */
.inventario-sucursal {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

/* ACCIONES */
.inventario-actions {
    display: flex;
    gap: 6px;
    
}

/* TALLAS */
.btn-tallas {
    font-size: 12px;
    border-radius: 10px;
    background: #2866d2;
    color: white;
    border: none;
    transition: 0.2s;

    padding: 6px 10px; /* controla el tamaño */
    width: auto;       /* evita que se estire */
}

.btn-tallas:hover {
    background: #2a61ae;
}

/* TALLAS */
.btn-tallas1 {
    font-size: 10px;
    border-radius: 10px;
    background: #2866d2;
    color: white;
    border: none;
    transition: 0.2s;

    padding: 6px 10px; /* controla el tamaño */
    width: auto;       /* evita que se estire */
}

.btn-tallas1:hover {
    background: #2a61ae;
}

.inventario-title {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

/* chip tipo etiqueta */
.sucursal-chip {
    font-size: 10px;
    padding: 3px 6px;
    border-radius: 6px;

    background: #f1f5f9;
    color: #475569;

    font-weight: 500;
}
</style>

