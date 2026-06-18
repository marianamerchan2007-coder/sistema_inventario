<?php 
session_start();
include("../config/conexion.php");

require_once '../vendor/autoload.php';
require_once '../config/cloudinary.php';

use Cloudinary\Api\Upload\UploadApi;

if ($_SERVER["REQUEST_METHOD"] == "POST"){ //Si se envió el formulario
    $nombre = trim($_POST['nombre_producto']);
    $nombre = mb_convert_case($nombre,MB_CASE_TITLE,"UTF-8");
    $categoria = $_POST['id_categoria'];
    $subcategoria = $_POST['id_subcategoria'];
    $estado = $_POST['estado'];
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO producto(
                nombre_producto,
                id_categoria,
                id_subcategoria,
                estado,
                precio,
                descripcion
            )
            VALUES(
                :nombre,
                :categoria,
                :subcategoria,
                :estado, 
                :precio,
                :descripcion
                )";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':categoria', $categoria);
    $stmt->bindParam(':subcategoria', $subcategoria);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':precio', $precio);
    $stmt->bindParam(':descripcion', $descripcion);

    // verificar si ya existe el producto
    $sqlVerificar = "SELECT COUNT(*) 
                    FROM producto 
                    WHERE LOWER(nombre_producto) = LOWER(:nombre)
                    AND id_categoria = :categoria";

    $stmtVerificar = $conexion->prepare($sqlVerificar);

    $stmtVerificar->bindParam(':nombre', $nombre);
    $stmtVerificar->bindParam(':categoria', $categoria);

    $stmtVerificar->execute();

    $existeProducto = $stmtVerificar->fetchColumn();

    if($existeProducto > 0){
        $_SESSION['error_producto'] =
            "Ya existe un producto con ese nombre en esta categoría.";
        header("Location: agregar_producto.php");
        exit();
    }

    // guardar producto
    $stmt->execute();

    $idProducto = $conexion->lastInsertId(); //traer el id del producto nuevo

    if(isset($_POST['talla'])){

    foreach($_POST['talla'] as $talla){

        $sqlTalla = "INSERT INTO producto_talla(
                        numero_talla,
                        id_producto
                    )
                    VALUES(
                        :talla,
                        :id_producto
                    )";

        $stmtTalla = $conexion->prepare($sqlTalla);

        $stmtTalla->bindParam(':talla', $talla);
        $stmtTalla->bindParam(':id_producto', $idProducto);

        $stmtTalla->execute();
    }
    }

    
    if(isset($_POST['colores'])){

        foreach($_POST['colores'] as $color){

            $color = trim($color);

            $color = mb_convert_case(
                $color,
                MB_CASE_TITLE,
                "UTF-8"
            );

            if(!empty($color)){

                $sqlColor = "INSERT INTO producto_color(
                                nombre_color,
                                id_producto
                            )
                            VALUES(
                                :color,
                                :id_producto
                            )";

                $stmtColor = $conexion->prepare($sqlColor);

                $stmtColor->bindParam(':color', $color);
                $stmtColor->bindParam(':id_producto', $idProducto);

                $stmtColor->execute();
            }
        }
    }

    
    // guardar imágenes
    if(!empty($_FILES['imagenes']['name'][0])){

        $configImagenes = json_decode(
            $_POST['imagenes_config'],
            true
        );

        $imagenesGuardar = [];

        foreach($_FILES['imagenes']['tmp_name'] as $key => $tmpName){

            if(empty($tmpName)){
                continue;
            }

            $nombreOriginal =
                $_FILES['imagenes']['name'][$key];

            // validar formato
            $mime = mime_content_type($tmpName);

            $permitidos = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if(!in_array($mime, $permitidos)){
                continue;
            }

            // validar tamaño
            if($_FILES['imagenes']['size'][$key] > 2 * 1024 * 1024){
                continue;
            }

            // buscar configuración
            $config = null;

            foreach($configImagenes as $cfg){

                if($cfg['nombre'] === $nombreOriginal){

                    $config = $cfg;

                    break;
                }
            }

            if(!$config){
                continue;
            }

            // nombre único
            $resultado = (new UploadApi())->upload(
                $tmpName,
                [
                    'folder' => 'calzado_web/productos'
                ]
            );

            $imagenesGuardar[] = [

                'ruta' => $resultado['secure_url'],

                'public_id' => $resultado['public_id'],

                'orden' => $config['orden'],

                'principal' => $config['principal'] ? 1 : 0
            ];
        }

        // ordenar antes de guardar
        usort($imagenesGuardar, function($a, $b){

            return $a['orden'] <=> $b['orden'];
        });

        foreach($imagenesGuardar as $img){

            $sqlImagen = "INSERT INTO producto_imagen(

                            ruta_imagen,
                            public_id,
                            id_producto,
                            orden,
                            principal

                        )
                        VALUES(

                            :ruta,
                            :public_id,
                            :id_producto,
                            :orden,
                            :principal
                        )";

            $stmtImagen = $conexion->prepare($sqlImagen);

            $stmtImagen->bindParam(':ruta', $img['ruta']);

            $stmtImagen->bindParam(':public_id', $img['public_id']);

            $stmtImagen->bindParam(':id_producto', $idProducto);

            $stmtImagen->bindParam(':orden', $img['orden']);

            $stmtImagen->bindParam(':principal', $img['principal']);

            $stmtImagen->execute();
        }
    }

    header("Location: productos.php?success=1&producto=" . urlencode($nombre));
    exit();
}

include("includes/auth.php");
include("includes/header.php");
include("includes/sidebar.php"); 
?>

<?php
$sql_categoria = "SELECT * FROM categorias";
$stmt = $conexion->query($sql_categoria);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_subcategoria ="SELECT * FROM subcategoria";
$stmt = $conexion->query($sql_subcategoria);
$subcategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="main">
    <div class="container-fluid">
        <div class="mb-3">
            <h5>Nuevo producto</h5>
        </div>

        <form action="agregar_producto.php" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="nombre" class="form-label">Nombre del producto</label>
                <input type="text" id="nombre" name="nombre_producto" placeholder="Ej. Sneaker Classic" class="form-control" required>
                <?php if(isset($_SESSION['error_producto'])): ?>
                    <small class="text-danger d-block mb-3">
                        <?= $_SESSION['error_producto']; ?>
                    </small>
                    <?php unset($_SESSION['error_producto']); ?>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="categoria" class="form-label">Categoría</label>
                    <select name="id_categoria" id="categoria" class="form-select" required>
                        <option value="">Seleccione categoría</option>
                        <?php foreach($categorias as $categoria): ?>

                            <option value="<?= $categoria['id_categoria']; ?>">
                                <?= $categoria['nombre_categoria']; ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-4">
                    <label for="subcategoria" class="form-label">Tipo</label>
                    <select name="id_subcategoria" id="subcategoria" class="form-select" required disabled>
                        <option value="">Seleccione una categoría primero</option>
                        <?php foreach($subcategorias as $subcategoria): ?>
                            <option 
                                value="<?= $subcategoria['id_subcategoria']; ?>"
                                data-categoria="<?= $subcategoria['id_categoria']; ?>">
                                <?= $subcategoria['nombre_subcategoria']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-4">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select" required>
                        <option value="Normal">Normal</option>
                        <option value="Nuevo">Nuevo</option>
                    </select>
                </div>

                <div class="col-md-6 mb-4">
                    <label for="precio" class="form-label">Precio</label>
                    <input type="number" name="precio" class="form-control"  placeholder="Ej: 50000" min="1" required>
                </div>

            </div>

            <div class="card border-1 mb-4">
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label mb-3">
                            Tallas disponibles
                        </label>

                        <div class="tallas-grid">
                            <?php $tallas = [28, 30, 32, 34, 35, 36, 38, 40, 42, 44];?>
                            <?php foreach($tallas as $talla): ?>
                                <div>
                                    <input type="checkbox"
                                        class="btn-check talla-check"
                                        name="talla[]"
                                        value="<?= $talla; ?>"
                                        id="t<?= $talla; ?>">
                                    <label class="talla-box"
                                        for="t<?= $talla; ?>">
                                        <?= $talla; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div id="errorTallas"
                            class="text-danger mt-3">
                        </div>
                    </div>

                    <div>
                        <label class="form-label mb-3">
                            Colores disponibles
                        </label>

                        <div id="contenedorColores" class="colores-grid">

                            <div class="input-group mb-2 color-item">

                                <input type="text"
                                    name="colores[]"
                                    class="form-control"
                                    placeholder="Ingrese un color"
                                    required>

                                <button type="button"
                                    class="btn btn-color"
                                    onclick="eliminarColor(this)">

                                    ✕

                                </button>

                            </div>

                        </div>

                        <button type="button"
                            class="btn btn-primary btn-sm mt-2"
                            id="btnAgregarColor">

                            + Agregar color

                        </button>

                    </div>
                </div>
            </div>

                <div class="mb-4">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="4" placeholder="Descripción del producto"></textarea>
                </div>

                <div class="mb-2">
                    <label class="form-label">
                        Imágenes del producto
                    </label>
                    <input type="file"
                        name="imagenes[]" id="imagenes"
                        multiple
                        class="form-control">
                </div>

            <div class="row mt-4" id="listaImagenes"></div>

            <div id="inputsOrden"></div>

            <input type="hidden" name="imagenes_config" id="imagenes_config">
            
            <div class="d-flex justify-content-end align-items-center gap-2 mt-4 acciones-formulario">
                <a href="productos.php" class="btn btn-light btn-sm"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1 text-light"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
// REFERENCIAS DEL DOM Y VARIABLES
const inputImagenes = document.getElementById('imagenes');
const listaImagenes = document.getElementById('listaImagenes');
const form = document.querySelector('form');

// Almacena las imágenes seleccionadas
let imagenesSeleccionadas = [];

// CARGA Y VALIDACIÓN DE IMÁGENES
inputImagenes.addEventListener('change', function(){

    imagenesSeleccionadas = [];
    listaImagenes.innerHTML = "";
    let nombres = [];

    // VALIDAR MÍNIMO 2 IMÁGENES
    if(this.files.length < 2){

        mostrarToast(
            "Debes seleccionar mínimo 2 imágenes.",
            "warning"
        );

        this.value = "";
        imagenesSeleccionadas = [];
        listaImagenes.innerHTML = "";

        return;
    }

    // VALIDAR MÁXIMO 6 IMÁGENES
    if(this.files.length > 6){

        mostrarToast(
            "Máximo 6 imágenes permitidas.",
            "warning"
        );
        this.value = "";

        return;
    }

    // Recorrer archivos seleccionados
    for(let i = 0; i < this.files.length; i++){

        let archivo = this.files[i];

        // Validar imágenes repetidas
        if(nombres.includes(archivo.name)){

            mostrarToast(
                "No puedes repetir imágenes.",
                "danger"
            );

            this.value = "";

            return;
        }

        nombres.push(archivo.name);

        // Validar tamaño máximo
        if(archivo.size > 2 * 1024 * 1024){

            mostrarToast(
                "Máximo 2 MB por imagen.",
                "danger"
            );

            this.value = "";

            return;
        }

        // Guardar configuración inicial
        imagenesSeleccionadas.push({

            archivo: archivo,

            nombre: archivo.name,

            orden: i + 1,

            principal: i === 0
        });
    }

    renderizarImagenes();
});


// RENDERIZAR TARJETAS DE IMÁGENES
function renderizarImagenes(){

    listaImagenes.innerHTML = `
        <div class="row g-3">
    `;

    imagenesSeleccionadas.forEach((img, index) => {

        let url = URL.createObjectURL(img.archivo);

        listaImagenes.innerHTML += `

        <div class="col-md-3 mb-4">

            <div class="card nueva-card border-0 shadow-sm h-100 rounded-4 overflow-hidden">

                <!-- imagen -->
                <div class="position-relative">

                    <img src="${url}"
                        class="w-100"
                        style="
                            height:220px;
                            object-fit:cover;
                        ">

                    ${
                        img.principal
                        ?
                        `
                        <span class="
                            badge
                            bg-body-tertiary
                            text-dark
                            position-absolute
                            top-0
                            start-0
                            m-2
                            px-3
                            py-2
                        ">
                            ⭐ Principal
                        </span>
                        `
                        :
                        ''
                    }

                </div>

                <!-- contenido -->
                <div class="card-body">

                    <!-- orden -->
                    <div class="mb-3">

                        <label class="form-label small text-muted mb-1">

                            Orden

                        </label>

                        <input type="number"
                            min="1"
                            value="${img.orden}"
                            class="form-control"
                            ${img.principal ? 'disabled' : ''}
                            onchange="actualizarOrden(${index}, this.value)">
                    </div>

                    <!-- principal -->
                    <div class="form-check mb-3">

                        <input type="radio"
                            class="form-check-input"
                            id="principal_${index}"
                            name="principal_temp"
                            ${img.principal ? 'checked' : ''}
                            onchange="hacerPrincipal(${index})">

                        <label class="form-check-label"
                            for="principal_${index}">

                            Imagen principal

                        </label>

                    </div>

                    <!-- eliminar -->
                    <button type="button"
                        class="btn btn-outline-danger btn-sm w-100"
                        onclick="eliminarImagen(${index})">

                        Eliminar imagen

                    </button>

                </div>

            </div>

        </div>
        `;
    });

    listaImagenes.innerHTML += `</div>`;

    guardarConfiguracion();
}

// ACTUALIZAR ORDEN DE IMÁGENES
function actualizarOrden(index, valor){

    valor = parseInt(valor);

    if(isNaN(valor)){

        valor = 2;
    }

    imagenesSeleccionadas[index].orden = valor;

    guardarConfiguracion();
}

// MARCAR IMAGEN PRINCIPAL
function hacerPrincipal(index){

    imagenesSeleccionadas.forEach((img, i) => {

        // si era principal antes y ahora ya no
        if(img.principal && i !== index){

            img.orden = 2;
        }

        // nueva principal
        img.principal = (i === index);

        if(img.principal){

            img.orden = 1;
        }
    });

    // Reorganizar órdenes secundarias
    reorganizarOrdenes();

    // Actualizar vista
    renderizarImagenes();
}

// REORGANIZAR ÓRDENES DE IMÁGENES
function reorganizarOrdenes(){

    let secundarias = imagenesSeleccionadas
        .filter(img => !img.principal);

    secundarias.sort((a, b) => a.orden - b.orden);

    secundarias.forEach((img, index) => {

        img.orden = index + 2;
    });
}

// ELIMINAR IMAGEN
function eliminarImagen(index){

    // Validar mínimo de imágenes
    if(imagenesSeleccionadas.length <= 2){

        mostrarToast(
            "Debes seleccionar mínimo 2 imágenes.",
            "warning"
        );

        return;
    }

    // Eliminar imagen
    imagenesSeleccionadas.splice(index, 1);

    // Verificar imagen principal
    if(imagenesSeleccionadas.length > 0){

        const principalExiste =
            imagenesSeleccionadas.some(
                img => img.principal
            );

        if(!principalExiste){

            imagenesSeleccionadas[0].principal = true;
            imagenesSeleccionadas[0].orden = 1;
        }
    }

    // Reorganizar y actualizar vista
    reorganizarOrdenes();
    renderizarImagenes();
}

// VALIDAR CONFIGURACIÓN DE IMÁGENES
function validarImagenes(){

    let ordenes = [];
    let principalCount = 0;

    for(let img of imagenesSeleccionadas){

        // Validar imagen principal
        if(img.principal){

            principalCount++;

            if(img.orden !== 1){

                mostrarToast(
                    "La imagen principal debe tener orden 1",
                    "danger"
                );

                return false;
            }

            continue;
        }

        // Validar orden mínimo
        if(img.orden < 2){

            mostrarToast(
                "Las imágenes secundarias deben iniciar desde 2",
                "danger"
            );

            return false;
        }

        // Validar órdenes repetidos
        if(ordenes.includes(img.orden)){

            mostrarToast(
                "No puedes repetir números de orden",
                "danger"
            );

            return false;
        }

        ordenes.push(img.orden);
    }

    // Validar una sola imagen principal
    if(principalCount !== 1){

        mostrarToast(
            "Debe existir una sola imagen principal",
            "danger"
        );

        return false;
    }

    // Validar órdenes consecutivos
    ordenes.sort((a, b) => a - b);

    for(let i = 0; i < ordenes.length; i++){

        const esperado = i + 2;

        if(ordenes[i] !== esperado){

            mostrarToast(
                `El orden debe ser consecutivo: ${esperado}`,
                "danger"
            );

            return false;
        }
    }

    return true;
}

// GUARDAR CONFIGURACIÓN DE IMÁGENES
function guardarConfiguracion(){

    document.getElementById('imagenes_config').value =
        JSON.stringify(

            imagenesSeleccionadas.map(img => ({

                nombre: img.nombre,
                orden: img.orden,
                principal: img.principal

            }))
        );
}

// VALIDACIÓN DEL FORMULARIO
form.addEventListener('submit', function(e){

    // Validar descripción
    const descripcion =
        document.getElementById('descripcion');

    if(descripcion.value.trim() === ''){

        e.preventDefault();

        descripcion.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        descripcion.focus();

        return;
    }

    // Validar imágenes
    if(imagenesSeleccionadas.length === 0){

        e.preventDefault();

        mostrarToast(
            "Debes seleccionar imágenes.",
            "warning"
        );

        return;
    }

    // Validar configuración de imágenes
    if(!validarImagenes()){

        e.preventDefault();
        return;
    }

    // Validar tallas
    const tallasSeleccionadas =
        document.querySelectorAll(
            '.talla-check:checked'
        );

    const errorTallas =
        document.getElementById('errorTallas');

    if(tallasSeleccionadas.length < 2){

        e.preventDefault();

        errorTallas.innerHTML =
            "Debes seleccionar mínimo 2 tallas.";

        errorTallas.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        return;
    }

    errorTallas.innerHTML = '';

    guardarConfiguracion();
});

// FILTRO DE SUBCATEGORÍAS
const categoria =
    document.getElementById('categoria');

const subcategoria =
    document.getElementById('subcategoria');

// Guardar opciones originales
const opcionesSubcategorias =
    Array.from(subcategoria.options);

categoria.addEventListener('change', function(){

    const categoriaSeleccionada =
        this.value;

    // Limpiar select
    subcategoria.innerHTML = '';

    // Si no seleccionó categoría
    if(categoriaSeleccionada === ''){

        subcategoria.disabled = true;

        subcategoria.innerHTML = `
            <option value="">
                Seleccione una categoría primero
            </option>
        `;

        return;
    }

    // Activar select
    subcategoria.disabled = false;

    // Opción por defecto
    subcategoria.innerHTML = `
        <option value="">
            Seleccione subcategoría
        </option>
    `;

    // Mostrar solo subcategorías de la categoría seleccionada
    opcionesSubcategorias.forEach(opcion => {

        if(
            opcion.dataset.categoria ===
            categoriaSeleccionada
        ){

            subcategoria.appendChild(
                opcion.cloneNode(true)
            );
        }
    });
});


// GESTIÓN DE COLORES
const contenedorColores =
    document.getElementById('contenedorColores');

const btnAgregarColor =
    document.getElementById('btnAgregarColor');

// Agregar nuevo campo de color
btnAgregarColor.addEventListener('click', function(){

    const nuevoColor =
        document.createElement('div');

    nuevoColor.classList.add(
        'input-group',
        'mb-2',
        'color-item'
    );

    nuevoColor.innerHTML = `

        <input type="text"
            name="colores[]"
            class="form-control"
            placeholder="Ingrese un color"
            required>

        <button type="button"
            class="btn btn-color"
            onclick="eliminarColor(this)">

            ✕

        </button>
    `;

    contenedorColores.appendChild(nuevoColor);
});

// Eliminar color
function eliminarColor(btn){

    const items =
        document.querySelectorAll('.color-item');

    // Mantener al menos un campo
    if(items.length <= 1){

        return;
    }

    btn.parentElement.remove();
}
</script>

<?php include("includes/footer.php"); ?>