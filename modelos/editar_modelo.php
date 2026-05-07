<?php include ("../config/conexion.php");
include('../includes/auth.php');
include('../includes/header.php');
include('../includes/sidebar.php');
?>

<?php
$id = $_GET['id']; 

$sql = "SELECT * FROM modelo WHERE id_modelo = :id";
$stmt = $conexion->prepare($sql);
$stmt->execute([":id" => $id]);

$fila = $stmt->fetch(PDO::FETCH_ASSOC);

// tallas
$sql_tallas = "SELECT numero_talla FROM talla WHERE id_modelo = :id";
$stmt_tallas = $conexion->prepare($sql_tallas);
$stmt_tallas->execute([":id" => $id]);

$tallas = $stmt_tallas->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="main">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="mb-0">Editar de modelo</h5>
            </div>
            
            <form action="procesar_editar.php" method="post" enctype="multipart/form-data">
            
                <input type="hidden" value="<?php echo $fila['id_modelo']; ?>" name= "txtId">

                <div class="modelo-box mb-2">
                    <div>
                        <h5><?= $fila['nombre_modelo']; ?></h5>
                        <small class="text-muted">Modelo registrado</small>
                    </div>

                    <img src="../image/<?= $fila['imagen']; ?>" class="modelo-img-right">

                </div>

                <div class="mb-2">
                    <label class="form-label" for="nombre">Nombre de modelo</label>
                    <input type="text" name="txtNombre" id="nombre" class="form-control" placeholder="Nombre del modelo" value="<?php echo $fila['nombre_modelo']; ?>">
                </div>

                
                <div class="mb-2">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea name="txtDescripcion" id="descripcion" class="form-control" rows="3" placeholder="Descripción del modelo"><?php echo $fila['descripcion']; ?></textarea>
                </div>

                <div class="mb-2">
                    <label class="form-label" for="origen">Origen</label>
                    <select name="txtOrigen" id="origen" class="form-select">
                        <option value="propio" <?= trim(strtolower($fila['origen_producto'])) === 'propio' ? 'selected' : '' ?>>Propio</option>
                        <option value="distribuidor" <?= trim(strtolower($fila['origen_producto'])) === 'distribuidor' ? 'selected' : '' ?>>Distribuidor</option>
                    </select>
                </div>
                
                
                <div class="mb-2">
                    <label class="form-label">Seleccione las tallas</label>
                    <div class="tallas-grid mt-2 mb-4">
                        <?php
                        $todas = [28,30,32,34,35,36,37,38,39,40,42,44];

                        foreach ($todas as $talla) {
                        ?>
                            <div class="talla-item">
                                <input type="checkbox" id="t<?= $talla ?>" name="txtTalla[]" value="<?= $talla ?>"
                                    <?= in_array($talla, $tallas) ? 'checked' : '' ?>>
                                <label for="t<?= $talla ?>"><?= $talla ?></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="mb-2"> 
                    <label class="form-label" for="imagen">Imagen</label> 
                    <input type="file" name="txtImagen" id="imagen" class="form-control" accept="image/*"> 
                    <input type="hidden" name="imagen_actual" value="<?= $fila['imagen']; ?>"> 
                </div>

    
                <div class="pt-2">
                    <input type="submit" value="Editar modelo" class="btn btn-primary ">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
</div>

<?php include('../includes/footer.php'); ?>
