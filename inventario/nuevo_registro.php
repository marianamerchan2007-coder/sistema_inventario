<?php
session_start();
include("../config/conexion.php");
include("../includes/auth.php");
include('../includes/header.php');
include('../includes/sidebar.php');

$id_rol = $_SESSION['rol'];
$id_sucursal_usuario = $_SESSION['sucursal'];
?>

<?php
$sql_modelos = "SELECT id_modelo, nombre_modelo, origen_producto, estado FROM modelo";
$stmt = $conexion->query($sql_modelos);
$modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM sucursal";
$stmt_sucursal = $conexion->query($sql);
$sucursales = $stmt_sucursal->fetchAll(PDO::FETCH_ASSOC);

$nombreSucursalUsuario = '';

foreach ($sucursales as $s) {
    if ($s['id_sucursal'] == $id_sucursal_usuario) {
        $nombreSucursalUsuario = $s['nombre_sucursal'];
        break;
    }
}
?>

<div class="main">
    <div class="container-fluid">
        <h5 class="mb-3">Registro de Inventario</h5>

        <form action="guardar_registro.php" method="POST">

            <!-- SUCURSAL -->
            <div class="mb-3">
                <label class="form-label">Sucursal</label>
                <?php if ($id_rol == 1): ?>
                    <!-- 👑 JEFE -->
                    <select name="txtSucursal" id="sucursal" class="form-select" required>
                        <option value="">Seleccione sucursal</option>

                        <?php foreach ($sucursales as $sucursal): ?>
                            <option value="<?= $sucursal['id_sucursal']; ?>">
                                <?= $sucursal['nombre_sucursal']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                <?php else: ?>
                    <!-- 👷 OPERARIO -->
                    <input type="hidden" name="txtSucursal" value="<?= $id_sucursal_usuario ?>">

                    <input type="text" class="form-control" 
                        value="<?= $nombreSucursalUsuario ?>" readonly>
                <?php endif; ?>
            </div>

            <!-- MODELO -->
            <div class="mb-3">
                <label class="form-label">Modelo</label>
                <select name="txtModelo" id="modelo" class="form-select" required>
                <option value="">Seleccione un modelo</option>

                <?php foreach ($modelos as $modelo): ?>
                <option value="<?= $modelo['id_modelo']; ?>"
                        data-origen="<?= $modelo['origen_producto'] ?>"
                        data-estado="<?= $modelo['estado']; ?>">
                    <?= $modelo['nombre_modelo']; ?>
                </option>
                <?php endforeach; ?>
                </select>
            </div>

            <!-- TALLA -->
            <div class="mb-3 mt-3">
                <div id="contenedor-tallas">
                    <div class="row mb-2 fila-talla">
                        <div class="col">
                            <label class="form-label">Talla</label>
                            <select name="tallas[]" id="talla" class="form-select talla-select" required>
                                <option value="">Seleccione talla</option>
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label">Cantidad actual</label>
                            <input type="number" class="form-control stock" readonly>
                        </div>

                        <!-- CANTIDAD -->
                        <div class="col">
                            <label class="form-label">Cantidad a registrar</label>
                            <input type="number" name="cantidades[]" class="form-control" min="1" required>
                        </div>

                        <div class="col-auto"> 
                            <button type="button" class="btn btn-danger eliminar"><i class="bi bi-trash3-fill"></i></button> 
                        </div>
                    </div>
                </div>
                <button type="button" id="agregarTalla" class="btn btn-success mt-4"> 
                    <i class="bi bi-plus-circle"></i> Agregar otra talla </button>

            </div>

            <button type="submit" class="btn btn-primary btn-custom">
                <i class="bi bi-check-circle"></i> Guardar todo
            </button>

            <a href="index.php" class="btn btn-secondary btn-custom">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script>
document.addEventListener("DOMContentLoaded", function () {

const sucursal = document.getElementById("sucursal");
const modelo = document.getElementById("modelo");
const contenedor = document.getElementById("contenedor-tallas");
const form = document.querySelector("form");

//  AGREGAR FILA
document.getElementById("agregarTalla").addEventListener("click", agregarFila);

function agregarFila() {

    let fila = document.querySelector(".fila-talla").cloneNode(true);

    // limpiar valores
    fila.querySelector(".talla-select").value = "";
    fila.querySelector(".stock").value = "";
    fila.querySelector("input[name='cantidades[]']").value = "";

    // eliminar fila
    fila.querySelector(".eliminar").addEventListener("click", () => fila.remove());

    contenedor.appendChild(fila);

    cargarTallas(fila);
}

//  CARGAR TALLAS
function cargarTallas(fila) {

    let select = fila.querySelector(".talla-select");
    let stockInput = fila.querySelector(".stock");

    let sucursalValue = sucursal 
        ? sucursal.value 
        : document.querySelector("input[name='txtSucursal']").value;

    if (!modelo.value || !sucursalValue) return;

    fetch(`get_tallas.php?modelo=${modelo.value}&sucursal=${sucursalValue}`)
    .then(res => res.json())
    .then(data => {

        select.innerHTML = '<option value="">Seleccione talla</option>';

        data.forEach(t => {

            const option = document.createElement("option");
            option.value = t.id_talla;
            option.textContent = t.numero_talla;
            option.setAttribute("data-stock", t.cantidad);

            select.appendChild(option);
        });

    });

    // mostrar stock
    select.addEventListener("change", () => {

        let selected = select.options[select.selectedIndex];
        stockInput.value = selected.getAttribute("data-stock") || 0;

    });
}

//  CUANDO CAMBIA MODELO O SUCURSAL
function recargarTodo() {
    document.querySelectorAll(".fila-talla").forEach(fila => {
        fila.querySelector(".talla-select").innerHTML = '<option value="">Cargando...</option>';
        fila.querySelector(".stock").value = "";
        cargarTallas(fila);
    });
}

modelo.addEventListener("change", recargarTodo);
if (sucursal) {
    sucursal.addEventListener("change", recargarTodo);
}

//  VALIDACIÓN FINAL (NO REPETIR TALLAS)
form.addEventListener("submit", function(e) {

    let tallas = document.querySelectorAll(".talla-select");
    let usadas = [];

    for (let t of tallas) {

        if (!t.value) {
            e.preventDefault();
            alert(" Selecciona todas las tallas");
            return;
        }

        if (usadas.includes(t.value)) {
            e.preventDefault();
            alert(" No puedes repetir la misma talla");
            return;
        }

        usadas.push(t.value);
    }

});

//  ACTIVAR PRIMERA FILA
cargarTallas(document.querySelector(".fila-talla"));

});

</script>

<style>
/*  FILA DE TALLA */
.fila-talla {
    background: #f1f5f9;
    border-radius: 14px;
    padding: 15px;
    margin-bottom: 10px;
    transition: 0.25s;
    border: 1px solid transparent;
}

.fila-talla:hover {
    background: #f5faff;
    border-color: #c7dcfe;
}

/*  ORGANIZACIÓN INTERNA */
.fila-talla .col {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.col-auto {
    display: flex;
    align-items: center;
    justify-content: center;
}

/*  INPUT STOCK */
.stock {
    background-color: #e2e8f0;
    font-weight: 600;
    text-align: center;
}

/*  BOTÓN ELIMINAR */
.eliminar {
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 14px;
}

/*  BOTÓN AGREGAR */
#agregarTalla {
    border-radius: 12px;
    font-weight: 600;
    background: #16a34a;
    border: none;
}

#agregarTalla:hover {
    opacity: 0.9;
}

/*  BOTONES PRINCIPALES */
.btn-custom {
    border-radius: 12px;
    padding: 10px 16px;
    font-weight: 600;
}

/*  RESPONSIVE */
@media (max-width: 768px) {

    .custom-card {
        padding: 15px;
    }

    .fila-talla {
        flex-direction: column;
    }

    .fila-talla .col,
    .fila-talla .col-auto {
        width: 100%;
        margin-bottom: 8px;
    }

}

@media (max-width: 768px) {

    .btn {
        padding: 8px 12px;
        font-size: 14px;
        border-radius: 8px;
    }

    .btn i {
        font-size: 13px;
    }

    /*  FILAS DE TALLA */
    .fila-talla {
        padding: 10px;
        border-radius: 10px;
    }

    /*  INPUTS Y SELECT */
    .form-control,
    .form-select {
        padding: 6px 10px;
        font-size: 14px;
    }

}

.col-auto {
        justify-content: flex-end;
    }

</style>


