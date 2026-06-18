<?php 
session_start();
include("../config/conexion.php");
include("includes/auth.php"); 
include("includes/header.php"); 
include("includes/sidebar.php");
?>

<?php
$id = $_GET['id'];
$sql = "SELECT p.id_producto, p.nombre_producto, c.nombre_categoria, c.id_categoria, s.nombre_subcategoria, s.id_subcategoria, p.precio, GROUP_CONCAT(t.numero_talla SEPARATOR ', ') AS tallas,
p.estado, p.descripcion FROM producto p 
INNER JOIN categorias c ON p.id_categoria = c.id_categoria
INNER JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
INNER JOIN producto_talla t ON p.id_producto = t.id_producto
WHERE p.id_producto = :id
GROUP BY p.id_producto";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$productos = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php
$sql_categoria = "SELECT * FROM categorias";
$stmtCategoria = $conexion->query($sql_categoria);
$categorias = $stmtCategoria->fetchAll(PDO::FETCH_ASSOC);

$sql_subcategoria ="SELECT * FROM subcategoria";
$stmtSubcategoria = $conexion->query($sql_subcategoria);
$subcategorias = $stmtSubcategoria->fetchAll(PDO::FETCH_ASSOC);

$sql_talla = "SELECT numero_talla FROM producto_talla WHERE id_producto = :id";
$stmtTallas= $conexion->prepare($sql_talla);
$stmtTallas->bindParam(':id', $id);
$stmtTallas->execute();
$tallas = $stmtTallas->fetchAll(PDO::FETCH_COLUMN);

$sql_color = "SELECT nombre_color FROM producto_color WHERE id_producto = :id";
$stmtColor = $conexion->prepare($sql_color);
$stmtColor->bindParam(':id', $id);
$stmtColor->execute();
$colores = $stmtColor->fetchAll(PDO::FETCH_COLUMN);

$sql_imagen = "SELECT * FROM producto_imagen WHERE id_producto = :id ORDER BY orden ASC";
$stmtImagen = $conexion->prepare($sql_imagen);
$stmtImagen->bindParam(':id', $id);
$stmtImagen->execute();
$imagenes = $stmtImagen->fetchAll(PDO::FETCH_ASSOC);

$sqlPrincipal="SELECT ruta_imagen FROM producto_imagen WHERE id_producto = :id and principal = 1 LIMIT 1";
$stmtPrincipal=$conexion->prepare($sqlPrincipal);
$stmtPrincipal->bindParam(':id', $id);
$stmtPrincipal->execute();
$imagenPrincipal = $stmtPrincipal->fetch(PDO::FETCH_ASSOC);
?>

<div class="main">
    <div class="container-fluid">
        <form action="actualizar_producto.php" method="post" enctype="multipart/form-data">
            <div id="paso1">
                <div class="encabezado-editar mb-3">
                    <h5 class="mb-1">Editar Producto</h5>
                    <a href="productos.php" class="btn btn-light border rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver
                    </a>
                </div>

                <!-- Información del producto -->
                <div class="card producto-box mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <img src="<?= $imagenPrincipal['ruta_imagen']; ?>"
                                alt="" class="producto-img me-4">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    <?= $productos['nombre_producto']; ?>
                                </h6>

                                <small class="text-muted d-block">
                                    <?= $productos['nombre_categoria']; ?>
                                    /
                                    <?= $productos['nombre_subcategoria']; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="id_producto" value="<?= $productos['id_producto']; ?>">

                <div class="d-flex align-items-center mb-3 mt-2">
                    <h6 class="text-dark mb-0">
                        Información General
                    </h6>
                    <hr class="flex-grow-1 ms-3">
                </div>

                <div class="mb-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre_producto" class="form-control" placeholder="Ej. Sneaker Classic" value="<?= $productos['nombre_producto'];?>">
                    
                    <?php if(isset($_SESSION['error_nombre'])): ?>
                        <small class="text-danger">
                            <?= $_SESSION['error_nombre']; ?>
                        </small>
                        <?php unset($_SESSION['error_nombre']); ?>
                    <?php endif; ?>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="mb-4">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select name="id_categoria" id="categoria" class="form-select">
                                <option value="">Seleccione categoría</option>

                                <?php foreach($categorias as $categoria): ?>

                                    <option value="<?= $categoria['id_categoria']; ?>"

                                        <?= ($categoria['id_categoria'] == $productos['id_categoria']) ? 'selected' : ''; ?>>

                                        <?= $categoria['nombre_categoria']; ?>

                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="subcategoria" class="form-label">Tipo</label>
                        <select name="id_subcategoria" id="subcategoria" class="form-select" value="">
                            <option value="">Seleccione subcategoría</option>
                            <?php foreach($subcategorias as $subcategoria): ?>
                                <option value="<?= $subcategoria['id_subcategoria']; ?>"
                                    data-categoria="<?= $subcategoria['id_categoria']; ?>"
                                    <?= ($subcategoria['id_subcategoria'] == $productos['id_subcategoria']) ? 'selected' : ''; ?>>
                                    <?= $subcategoria['nombre_subcategoria']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="Normal" <?= ($productos['estado'] == 'Normal') ? 'selected' : ''; ?>>Normal</option>
                            <option value="Nuevo"<?= ($productos['estado'] == 'Nuevo') ? 'selected' : ''; ?>>Nuevo</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" name="precio" step="0.01" class="form-control" placeholder="Ej: 50000" value="<?= $productos['precio']; ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label mb-3">Tallas disponibles</label>

                    <div class="tallas-grid">
                        <?php $tallasDisponibles = [28, 30, 32, 34, 35, 36, 38, 40, 42, 44]; ?>
                        <?php foreach($tallasDisponibles as $talla): ?>
                            <div>
                                <input type="checkbox"
                                    class="btn-check talla-check"
                                    name="talla[]"
                                    value="<?= $talla; ?>"
                                    id="t<?= $talla; ?>"

                                    <?= in_array($talla, $tallas) ? 'checked' : ''; ?>
                                >

                                <label class="talla-box"
                                    for="t<?= $talla; ?>">
                                    <?= $talla; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <div id="errorTallas"
                            class="text-danger mt-2">
                        </div>
                    </div>
                </div>


                <div class="mb-4">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="4" placeholder="Descripción del producto"><?= $productos['descripcion']?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label mb-3">Colores disponibles</label>

                    <div id="contenedorColores">
                        <?php if(!empty($colores)): ?>
                            <?php foreach($colores as $color): ?>
                                <div class="input-group mb-2 color-item">
                                    <input type="text"
                                        name="colores[]"
                                        class="form-control"
                                        placeholder="Ingrese un color"
                                        value="<?= $color; ?>"
                                        >
                                    <button type="button"
                                        class="btn btn-color"
                                        onclick="eliminarColor(this)">
                                        ✕
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="input-group mb-2 color-item">
                                <input type="text"
                                    name="colores[]"
                                    class="form-control"
                                    placeholder="Ingrese un color"
                                    >

                                <button type="button"
                                    class="btn btn-color"
                                    onclick="eliminarColor(this)">
                                    ✕
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button"
                        class="btn btn-primary mt-4"
                        id="btnAgregarColor">
                        + Agregar color
                    </button>
                </div>

                <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                    <button type="submit"
                        class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-1 text-light"></i>
                        Guardar cambios
                    </button>
                    <button type="button"
                        id="btnSiguiente"
                        class="btn btn-primary px-4">
                        Siguiente
                        <i class="bi bi-arrow-right ms-1 text-light"></i>
                    </button>
                </div>
            </div>

            <div id="paso2" class="d-none">

                <h6 class="fw-bold mb-0 bg-light p-3 rounded mb-3">
                    Editar Producto
                    <small class="text-muted fw-normal ms-2">
                        Gestión de imágenes
                    </small>
                </h6>
        
                <!-- Imágenes -->
                <div class="mb-4">
                    <div class="bg-light p-2 px-3 rounded border-start border-4 border-primary mb-3 d-inline-block">
                        <h6 class="mb-0">
                            Imágenes actuales
                        </h6>
                    </div>
                    
                    <div class="imagenes-wrapper p-3 rounded-4">
                        <div class="row g-3" id="contenedorImagenesActuales">

                            <?php foreach($imagenes as $imagen): ?>

                                <div class="col-md-2 imagen-item"
                                    data-id="<?= $imagen['id_imagen']; ?>">

                                    <div class="card border border-light shadow-sm rounded-3 overflow-hidden h-100 position-relative">

                                        <img src="<?= $imagen['ruta_imagen']; ?>"
                                            class="card-img-top"
                                            style="
                                                height:190px;
                                                object-fit:cover;
                                            ">
            
                                        <?php if($imagen['principal'] == 1): ?>

                                            <span class="
                                                badge-principal
                                                badge
                                                rounded-pill
                                                bg-body-tertiary
                                                position-absolute
                                                top-0
                                                start-0
                                                m-2
                                                px-3
                                                py-2
                                                shadow-sm
                                                text-dark
                                            ">

                                                ⭐ Principal

                                            </span>

                                        <?php endif; ?>


                                        <div class="card-body">

                                            <div class="contenedor-orden">
                                                
                                                <label class="form-label small text-muted"> Orden </label>
                                                
                                                <input type="number"
                                                    name="orden_imagen[<?= $imagen['id_imagen']; ?>]"
                                                    value="<?= $imagen['orden']; ?>"
                                                    class="form-control mb-3 orden-input"
                                                    <?= $imagen['principal'] == 1 ? 'readonly' : ''; ?>>

                                            </div>

                                            <div class="form-check mb-2">

                                                <input type="radio"
                                                    name="imagen_principal"
                                                    value="<?= $imagen['id_imagen']; ?>"

                                                    <?= ($imagen['principal'] == 1)
                                                        ? 'checked'
                                                        : ''; ?>

                                                    class="form-check-input radio-principal"
                                                    id="principal<?= $imagen['id_imagen']; ?>">

                                                <label class="form-check-label"
                                                    for="principal<?= $imagen['id_imagen']; ?>">
                                                    Imagen principal
                                                </label>

                                            </div>

                                            <div class="form-check d-none">

                                                <input type="checkbox"
                                                    name="eliminar_imagenes[]"
                                                    value="<?= $imagen['id_imagen']; ?>"
                                                    class="form-check-input checkbox-eliminar"

                                                    id="eliminar<?= $imagen['id_imagen']; ?>">

                                            </div>

                                            <button type="button" 
                                                class="btn btn-outline-danger w-100 btn-eliminarImagen rounded-3"
                                                data-checkbox="eliminar<?= $imagen['id_imagen']; ?>"> 
                                                Eliminar imagen 
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div id="errorImagenes" class="text-danger mb-4"></div>

                    
                <div class="mb-2">
                    <div class="bg-light p-2 px-3 rounded border-start border-4 border-primary mb-3 d-inline-block">
                        <h6 class="mb-0">
                            Agregar nuevas imágenes
                        </h6>
                    </div>

                    <input type="file"
                        name="imagenes[]"
                        id="imagenes" accept="image/*"
                        multiple
                        class="form-control">
                </div>

                <div id="listaImagenes" class="row mb-4"></div>

                <div class="d-flex justify-content-end align-items-center gap-2 mt-4">

                    <button type="button"
                        id="btnAnterior"
                        class="btn btn-primary px-4">
                        <i class="bi bi-arrow-left me-1 text-light"></i>
                        Anterior
                    </button>

                    <a href="productos.php"
                        class="btn btn-light border px-4">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </a>

                    <button type="submit"
                        class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-1 text-light"></i>
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// =====================================
// REFERENCIAS DEL DOM Y VARIABLES
// =====================================

const form =
    document.querySelector('form');

const inputImagenes =
    document.getElementById('imagenes');

const listaImagenes =
    document.getElementById('listaImagenes');

// imágenes actuales BD
const imagenesExistentes =
    <?= count($imagenes); ?>;

let nuevasImagenes = [];


// =====================================
// UTILIDADES DE TOASTS
// =====================================

function toastError(mensaje){

    mostrarToast(
        `${mensaje}`,
        'danger'
    );
}

function toastWarning(mensaje){

    mostrarToast(
        `${mensaje}`,
        'warning'
    );
}

function toastSuccess(mensaje){

    mostrarToast(
        `${mensaje}`,
        'success'
    );
}


// =====================================
// CAMBIO INPUT IMÁGENES
// =====================================

inputImagenes.addEventListener('change', function(){

    listaImagenes.innerHTML = "";

    nuevasImagenes = [];

    let nombres = [];

    let nuevas =
        this.files.length;

    let eliminadas =
        document.querySelectorAll(
            'input[name="eliminar_imagenes[]"]:checked'
        ).length;

    let totalFinal =
        imagenesExistentes +
        nuevas -
        eliminadas;

    // máximo
    if(totalFinal > 6){

        toastWarning(
            "Solo puedes tener máximo 6 imágenes."
        );

        this.value = "";

        return;
    }

    for(let i = 0; i < this.files.length; i++){

        let archivo = this.files[i];

        // repetidas
        if(nombres.includes(archivo.name)){

            toastError(
                "No puedes seleccionar imágenes repetidas."
            );

            this.value = "";

            return;
        }

        nombres.push(archivo.name);

        // tamaño
        if(archivo.size > 2 * 1024 * 1024){

            toastError(
                "Cada imagen debe pesar máximo 2MB."
            );

            this.value = "";

            return;
        }

        nuevasImagenes.push({

            archivo: archivo,

            nombre: archivo.name
        });
    }

    renderNuevas();
});


// =====================================
// RENDER NUEVAS IMÁGENES
// =====================================

function renderNuevas(){

    let html = `<div class="row g-3">`;

    nuevasImagenes.forEach((img) => {

        let url = URL.createObjectURL(img.archivo);

        html += `
        <div class="col-12 col-sm-6 col-md-3">

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 nueva-card">

                <img src="${url}"
                    class="w-100"
                    style="height:220px; object-fit:cover;">

                <div class="card-body text-center">
                    <small class="text-muted">Imagen lista para guardar</small>
                </div>

            </div>

        </div>
        `;
    });

    html += `</div>`;

    listaImagenes.innerHTML = html;
}


// =====================================
// VALIDAR FORMULARIO
// =====================================

form.addEventListener('submit', function(e){

    const nombre =
        document.getElementById('nombre')
            .value
            .trim();

    const descripcion =
        document.getElementById('descripcion')
            .value
            .trim();

    const precio =
        document.querySelector('input[name="precio"]')
            .value
            .trim();

    // Nombre
    if(nombre === ''){

        e.preventDefault();

        toastWarning(
            "Debes ingresar el nombre del producto."
        );

        document.getElementById('nombre').focus();

        return;
    }

    // Descripción
    if(descripcion === ''){

        e.preventDefault();

        toastWarning(
            "Debes ingresar la descripción del producto."
        );

        document.getElementById('descripcion').focus();

        return;
    }

    // Precio
    if(precio === '' || parseFloat(precio) <= 0){

        e.preventDefault();

        toastWarning(
            "Debes ingresar un precio válido."
        );

        document.querySelector('input[name="precio"]').focus();

        return;
    }

    // Categoría
    const categoria =
        document.getElementById('categoria').value;

    if (categoria === '') {

        e.preventDefault();

        toastWarning(
            "Debes seleccionar una categoría."
        );

        document.getElementById('categoria').focus();

        return;
    }

    // Subcategoría (tipo)
    const subcategoria =
        document.getElementById('subcategoria').value;

    if (subcategoria === '') {

        e.preventDefault();

        toastWarning(
            "Debes seleccionar un tipo (subcategoría)."
        );

        document.getElementById('subcategoria').focus();

        return;
    }

    // Colores
    const coloresInputs =
        document.querySelectorAll('input[name="colores[]"]');

    let colorVacio = false;

    // validar si hay vacíos
    coloresInputs.forEach(input => {

        if (input.value.trim() === '') {

            colorVacio = true;

            // opcional: marcar visualmente el input vacío
            input.classList.add('is-invalid');
        } else {

            input.classList.remove('is-invalid');
        }
    });

    if (colorVacio) {

        e.preventDefault();

        toastWarning(
            "Todos los colores deben estar diligenciados."
        );

        return;
    }

    // tallas
    const tallasSeleccionadas =
        document.querySelectorAll(
            'input[name="talla[]"]:checked'
        );

    if(tallasSeleccionadas.length < 2){

        e.preventDefault();

        toastWarning(
            "Debes seleccionar mínimo 2 tallas."
        );

        return;
    }

    let nuevas =
        inputImagenes.files.length;

    let eliminadas =
        document.querySelectorAll(
            'input[name="eliminar_imagenes[]"]:checked'
        ).length;

    let totalFinal =
        imagenesExistentes +
        nuevas -
        eliminadas;

    // mínimo
    if(totalFinal < 2){

        e.preventDefault();

        toastWarning(
            "El producto debe tener mínimo 2 imágenes."
        );

        return;
    }

    // máximo
    if(totalFinal > 6){

        e.preventDefault();

        toastWarning(
            "Máximo 6 imágenes permitidas."
        );

        return;
    }

    const ordenInputs =
        document.querySelectorAll('input[name^="orden_imagen["]');

    let ordenInvalido = false;

    ordenInputs.forEach(input => {

        const valor = parseInt(input.value);

        if (valor === 1) {

            // validar si NO es la principal
            const id = input.name.match(/\d+/)[0];

            const radioPrincipal =
                document.querySelector(
                    `input[name="imagen_principal"][value="${id}"]`
                );

            if (!radioPrincipal || !radioPrincipal.checked) {

                ordenInvalido = true;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        }
    });

    if (ordenInvalido) {

        e.preventDefault();

        toastWarning(
            "Las imágenes secundarias deben iniciar desde 2."
        );

        return;
    }

});


// =====================================
// CAMBIAR IMAGEN PRINCIPAL
// =====================================

const radiosPrincipal =
    document.querySelectorAll(
        '.radio-principal'
    );

radiosPrincipal.forEach(radio => {

    radio.addEventListener('change', function(){

        const items =
            document.querySelectorAll(
                '.imagen-item'
            );

        let orden = 2;

        items.forEach(item => {

            const radioItem =
                item.querySelector(
                    '.radio-principal'
                );

            const contenedorOrden =
                item.querySelector(
                    '.contenedor-orden'
                );

            const card =
                item.querySelector('.card');

            const idImagen =
                radioItem.value;

            // limpiar badge anterior
            const badgeAnterior =
                item.querySelector(
                    '.badge-principal'
                );

            if(badgeAnterior){

                badgeAnterior.remove();
            }

            // SI ES PRINCIPAL
            if(radioItem.checked){

                contenedorOrden.classList.add('mb-3');

                // agregar badge moderno
                const badge =
                    document.createElement('span');

                badge.className = `
                    badge-principal
                    badge
                    rounded-pill
                    bg-body-tertiary
                    position-absolute
                    top-0
                    start-0
                    m-2
                    px-3
                    py-2
                    shadow-sm
                    text-dark
                `;

                badge.innerHTML =
                    `⭐ Principal`;

                card.querySelector(
                    '.card-img-top'
                ).parentElement.appendChild(
                    badge
                );

                
                contenedorOrden.innerHTML = `

                    <label class="form-label small text-muted">
                        Orden 
                    </label>

                    <input type="number"
                        value="1"
                        class="form-control bg-light "
                        disabled my-4>
                `;
            }
            else{

                contenedorOrden.innerHTML = `

                    <label class="form-label small text-muted">

                        Orden

                    </label>

                    <input type="number"
                        name="orden_imagen[${idImagen}]"
                        value="${orden}"
                        class="form-control rounded-3 my-2">
                `;

                orden++;
            }
        });

        toastSuccess(
            "Imagen principal actualizada."
        );
    });
});


// =====================================
// COLORES
// =====================================

const contenedorColores =
    document.getElementById(
        'contenedorColores'
    );

const btnAgregarColor =
    document.getElementById(
        'btnAgregarColor'
    );


// agregar color
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
            class="form-control rounded-start-3"
            placeholder="Ingrese un color">

        <button type="button"
            class="btn btn-color"
            onclick="eliminarColor(this)">
            ✕
        </button>
    `;

    contenedorColores.appendChild(nuevoColor);

    // 👇 foco automático en el nuevo input
    nuevoColor.querySelector('input').focus();

    toastSuccess("Color agregado.");
});


// eliminar color

function eliminarColor(btn){

    const items =
        document.querySelectorAll(
            '.color-item'
        );

    if(items.length <= 1){

        toastWarning(
            "Debe existir mínimo un color."
        );

        return;
    }

    btn.parentElement.remove();

    toastSuccess(
        "Color eliminado."
    );
}


// ======================================
// FILTRO SUBCATEGORÍAS
// ======================================

// guardar opciones originales
const opcionesSubcategorias =
    Array.from(
        subcategoria.options
    );

// subcategoría actual
const subcategoriaActual =
    "<?= $productos['id_subcategoria']; ?>";

function filtrarSubcategorias(){

    const categoriaSeleccionada =
        categoria.value;

    subcategoria.innerHTML = '';

    let tieneOpciones = false;

    opcionesSubcategorias.forEach(opcion => {

        if(
            opcion.dataset.categoria ===
            categoriaSeleccionada
        ){

            const nuevaOpcion =
                opcion.cloneNode(true);

            // seleccionar actual
            if(
                nuevaOpcion.value ==
                subcategoriaActual
            ){
                nuevaOpcion.selected = true;
            }

            subcategoria.appendChild(
                nuevaOpcion
            );

            tieneOpciones = true;
        }
    });

    // si no hay
    if(!tieneOpciones){

        subcategoria.innerHTML = `

            <option value="">

                Seleccione subcategoría

            </option>
        `;
    }
}


// cambio categoría
categoria.addEventListener(
    'change',
    filtrarSubcategorias
);


// cargar página
window.addEventListener(
    'DOMContentLoaded',
    filtrarSubcategorias
);


// =====================================
// BOTÓN ELIMINAR IMAGEN
// =====================================

const botonesEliminar =
    document.querySelectorAll(
        '.btn-eliminarImagen'
    );

botonesEliminar.forEach(btn => {

    btn.addEventListener('click', function(){

        const idCheckbox =
            this.dataset.checkbox;

        const checkbox =
            document.getElementById(
                idCheckbox
            );

        checkbox.checked =
            !checkbox.checked;

        // ACTIVADO
        if(checkbox.checked){

            this.classList.remove(
                'btn-outline-danger'
            );

            this.classList.add(
                'btn-danger'
            );

            this.innerHTML = `
                <i class="bi bi-check-circle me-1 text-white"></i>
                Imagen marcada
            `;
        }

        // DESACTIVADO
        else{

            this.classList.remove(
                'btn-danger'
            );

            this.classList.add(
                'btn-outline-danger'
            );

            this.innerHTML = `
                Eliminar imagen
            `;
        }
    });
});

// =====================================
// NAVEGACIÓN ENTRE PASOS DEL FORMULARIO
// =====================================

const paso1 =
    document.getElementById('paso1');

const paso2 =
    document.getElementById('paso2');

const btnSiguiente =
    document.getElementById('btnSiguiente');

const btnAnterior =
    document.getElementById('btnAnterior');


// =====================================
// IR A IMÁGENES
// =====================================

btnSiguiente.addEventListener('click', () => {

    paso1.classList.add('d-none');

    paso2.classList.remove('d-none');

});


// =====================================
// VOLVER A INFORMACIÓN
// =====================================

btnAnterior.addEventListener('click', () => {
    paso2.classList.add('d-none');
    paso1.classList.remove('d-none');
});

window.addEventListener('DOMContentLoaded', () => {

    const paso =
        "<?= $_GET['paso'] ?? 1 ?>";

    if(paso == 2){

        document
            .getElementById('paso1')
            .classList.add('d-none');

        document
            .getElementById('paso2')
            .classList.remove('d-none');
    }

});
</script>

<?php include("includes/footer.php"); ?>
