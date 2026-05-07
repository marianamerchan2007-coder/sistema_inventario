<?php 
include ("../config/conexion.php"); 
include('../includes/auth.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['txtNombre'] ?? '';
    $descripcion = $_POST['txtDescripcion'] ?? '';
    $origen = $_POST['txtOrigen'] ?? '';
    $tallas = $_POST['txtTalla'] ?? [];
    $imagen = $_FILES['txtImagen'] ?? null;

    $nombreImagen = "";

    if ($imagen && $imagen['error'] == 0) {

        $nombreImagen = time() . "_" . basename($imagen['name']);

        $carpeta = __DIR__ . "/../uploads/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $rutaDestino = $carpeta . $nombreImagen;

        if (!move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
            die("❌ No se pudo subir la imagen");
        }
    }


    try {

        $stmt = $conexion->prepare("
            INSERT INTO modelo (nombre_modelo, descripcion, origen_producto, imagen) 
            VALUES (:nombre, :descripcion, :origen, :imagen)
        ");

        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':origen' => $origen,
            ':imagen' => $nombreImagen
        ]);

        $idModelo = $conexion->lastInsertId();

        foreach ($tallas as $talla) {

            $stmtTalla = $conexion->prepare("
                INSERT INTO talla (id_modelo, numero_talla) 
                VALUES (:id_modelo, :talla)
            ");

            $stmtTalla->execute([
                ':id_modelo' => $idModelo,
                ':talla' => $talla
            ]);
        }

        header("Location: index.php?success=1&modelo=" . urlencode($nombre));
        exit();

    } catch (PDOException $e) {

    // error por duplicado (clave única)
    if ($e->getCode() == 23000) {
        header("Location: nuevo_modelo.php?error=duplicado");
        exit();
    } else {
        echo " Error: " . $e->getMessage();
    }
}
}

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<div class="main">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="mb-3">Registro de Modelos </h5>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'duplicado'): ?>
                <div class="alert alert-danger">
                    Este modelo ya existe en el sistema
                </div>
            <?php endif; ?>

            <form action="nuevo_modelo.php" method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label class="form-label" for="nombre">Nombre de modelo</label>
                    <input type="text" name="txtNombre" id="nombre" class="form-control" placeholder="Nombre del modelo" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="descripcion">Descripción</label>
                    <textarea name="txtDescripcion" id="descripcion" class="form-control" placeholder="Descripción" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="origen">Origen del modelo</label>
                    <select name="txtOrigen" id="origen" class="form-select">
                        <option value="propio" selected>Propio</option>
                        <option value="distribuidor">Distribuidor</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Seleccione las tallas</label>
                    <div class="tallas-grid mt-2 mb-4" id="grupoTallas">

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="28" id="t28">
                            <label for="t28">28</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="30" id="t30">
                            <label for="t30">30</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="32" id="t32">
                            <label for="t32">32</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="34" id="t34">
                            <label for="t34">34</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="35" id="t35">
                            <label for="t35">35</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="36" id="t36">
                            <label for="t36">36</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="37" id="t37">
                            <label for="t37">37</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="38" id="t38">
                            <label for="t38">38</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="39" id="t39">
                            <label for="t39">39</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="40" id="t40">
                            <label for="t40">40</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="42" id="t42">
                            <label for="t42">42</label>
                        </div>

                        <div class="talla-item">
                            <input type="checkbox" name="txtTalla[]" value="44" id="t44">
                            <label for="t44">44</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label" for="imagen">Imagen</label>
                    <input type="file" name="txtImagen" id="imagen" class="form-control" accept="image/*" required>

                    <div id="errorImagen" class="text-danger mb-2" style="display:none;">
                        Debes seleccionar una imagen
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar Modelo</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
    
<?php include('../includes/footer.php'); ?>

<script>
//Agregar talla
document.querySelector("form").addEventListener("submit", function(e) {
    const tallas = document.querySelectorAll('input[name="txtTalla[]"]:checked');

    if (tallas.length === 0) {
        e.preventDefault();
        alert("⚠️ Debe seleccionar al menos una talla");
    }
});
</script>
