<?php
session_start();
include('../config/conexion.php');
include('../includes/auth.php');
?>
<?php
$sql_local = "SELECT id_sucursal FROM sucursal WHERE nombre_sucursal = 'Local' LIMIT 1";
$stmt = $conexion->query($sql_local);
$id_local = $stmt->fetchColumn();

$sql = "SELECT id_modelo, nombre_modelo FROM modelo";
$stmt = $conexion->query($sql);
$modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('../includes/header.php');
include('../includes/sidebar.php');
?>

<div class="main">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"> Nueva transferencia </h4>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="guardar_transferencia.php" method="POST">
                <div class="mb-3">
                <label class="form-label">Modelo</label>
                    <select id="modelo" name="id_modelo" class="form-select" required>
                        <option value="">Seleccione modelo</option>
                        <?php foreach ($modelos as $m): ?>
                            <option value="<?= $m['id_modelo']; ?>">
                                <?= $m['nombre_modelo']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- TALLA -->
                <div class="mb-3">
                    <label>Talla</label>
                    <select name="id_inventario_origen" id="talla" class="form-select" required>
                        <option value="">Seleccione talla</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Cantidad disponible</label>
                    <input type="number" id="stock" class="form-control" readonly>
                </div>

                <!-- CANTIDAD -->
                <div class="mb-3">
                    <label>Cantidad a transferir</label>
                    <input type="number" name="cantidad" class="form-control" min="1" required>
                </div>

                <!-- DESTINO -->
                <input type="hidden" name="id_sucursal_destino" value="<?= $id_local ?>">

                <div class="mb-4">
                    <label>Sucursal destino</label>
                    <input type="text" class="form-control" value="Local" disabled>
                </div>

                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Guardar transferencia
                </button>
            </form>
        </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const modelo = document.getElementById("modelo");
    const talla = document.getElementById("talla");
    const stockInput = document.getElementById("stock");

    modelo.addEventListener("change", function () {

        let id_modelo = this.value;
        let id_sucursal = 1; // 👈 PLANTA SIEMPRE

        talla.innerHTML = '<option>Cargando...</option>';
        stockInput.value = "";

        if (!id_modelo) {
            talla.innerHTML = '<option value="">Seleccione talla</option>';
            return;
        }

        fetch("get_tallas.php?modelo=" + id_modelo + "&sucursal=" + id_sucursal)
        .then(res => res.json())
        .then(data => {

            console.log("DATA:", data); // debug

            talla.innerHTML = '<option value="">Seleccione talla</option>';

            if (data.length === 0) {
                talla.innerHTML = '<option value="">Sin stock disponible</option>';
                return;
            }

            data.forEach(item => {
                talla.innerHTML += `
                    <option value="${item.id_inventario}" data-stock="${item.cantidad_disponible}">
                        Talla ${item.numero_talla}
                    </option>
                `;
            });

        })
        .catch(error => {
            console.error(error);
            talla.innerHTML = '<option>Error al cargar</option>';
        });

    });

    talla.addEventListener("change", function () {
        let selected = this.options[this.selectedIndex];
        let stock = selected.getAttribute("data-stock");
        stockInput.value = stock ? stock : 0;
    });

});
</script>


