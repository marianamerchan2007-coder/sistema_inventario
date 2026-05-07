<?php
session_start();
include("../config/conexion.php");
include("../includes/auth.php");
include('../includes/header.php');
include('../includes/sidebar.php');

if ($_SESSION['rol'] != 1) {
    header("Location: ../inventario/index.php");
    exit;
}
?>

<?php
$buscar = $_GET['buscar'] ?? '';

$sql = "SELECT id_modelo, nombre_modelo, descripcion, origen_producto, imagen 
        FROM modelo 
        WHERE nombre_modelo LIKE :buscar";

$stmt = $conexion->prepare($sql);
$stmt->bindValue(':buscar', "%$buscar%", PDO::PARAM_STR);
$stmt->execute();

$modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <div class="main">
        <div class="container-fluid">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                    <h5 class="mb-2 mb-md-0">Modelos</h5>
                    
                    <div class="d-flex flex-column flex-sm-row gap-2 ms-md-auto">
                        <form method="GET" class="search-box d-flex">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search"></i>
                            </button>
                            <input
                                type="text"
                                name="buscar"
                                class="form-control form-control-sm"
                                placeholder="Buscar por modelo"
                                value="<?= htmlspecialchars($buscar) ?>">
                        </form>
        
                        <a href="nuevo_modelo.php" class="btn btn-primary btn-nuevo">+ Nuevo modelo</a>
                    </div>
            </div>
                

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> Modelo <strong><?= htmlspecialchars($_GET['modelo']) ?></strong> registrado correctamente
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($buscar != '' && empty($modelos)): ?>
                    <div class="alert alert-warning">
                        No se encontraron modelos para 
                        <strong><?= htmlspecialchars($buscar) ?></strong>
                    </div>
                <?php endif; ?>

                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Modelo</th>
                                <th>Origen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modelos as $modelo): ?>
                                <tr>
                                    <td>
                                        <img src="/uploads/<?php echo $modelo['imagen']; ?>"
                                        style="width: 100px; height: 90px; object-fit: cover; border-radius: 10px;">
                                    </td>

                                    <td class="text-start">
                                        <strong><?php echo $modelo['nombre_modelo']; ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $modelo['descripcion']; ?>
                                        </small>
                                    </td>

                                    <td>
                                        <span class="<?= $modelo['origen_producto'] == 'propio' ? 'badge-propio' : 'badge-dist' ?>">
                                            <?= $modelo['origen_producto']; ?> 
                                        </span>
                                    </td>

                                    <td>
                                        <a href="editar_modelo.php?id=<?php echo $modelo['id_modelo']; ?>" class="btn btn-editar btn-sm">Editar  <i class="bi bi-pencil-fill"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- ===== MOBILE (CARDS) ===== -->
                <div class="d-md-none">
                    <?php foreach ($modelos as $modelo): ?>
                        <div class="modelo-card-mobile">
                            
                            <img src="../image/<?php echo $modelo['imagen']; ?>" class="modelo-img-mobile">

                            <div class="modelo-info-mobile">
                                <h6><?php echo $modelo['nombre_modelo']; ?></h6>
                                <p><?php echo $modelo['descripcion']; ?></p>

                                <span class="<?= $modelo['origen_producto'] == 'propio' ? 'badge-propio' : 'badge-dist' ?>">
                                    <?= $modelo['origen_producto']; ?>
                                </span>

                                <a href="editar_modelo.php?id=<?php echo $modelo['id_modelo']; ?>" class="btn btn-editar w-100 mt-2">
                                    Editar <i class="bi bi-pencil-fill"></i>
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>

<style>
 /* ===== BASE ===== */
.img-tabla {
    width: 80px;
    height: 70px;
    object-fit: cover;
    border-radius: 10px;
}

/* ===== MOBILE CARDS ===== */
.modelo-card-mobile {
    display: flex;
    gap: 8px;
    background: #fff;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    flex-direction: column;
    overflow: hidden;
}
.modelo-img-mobile {
    width: 100%;
    height: 140px;
    object-fit: cover;
    border-radius: 12px 12px 0 0;
}

.modelo-info-mobile {
    width: 100%;
    display: flex;
    flex-direction: column;
}

.modelo-info-mobile h6 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.modelo-info-mobile p {
    font-size: 12px;
    color: #64748b;
    margin: 3px 0;
}
</style>

