<?php 
session_start();
include ("../config/conexion.php");
include("../includes/auth.php");

// guardar mensaje temporal
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);


//  QR correctamente definido
$qr = isset($_GET['qr']) ? trim($_GET['qr']) : null;

$datos = [];

if ($qr) {

    $sql = "SELECT i.id_inventario, i.cantidad_disponible,
                   m.nombre_modelo, m.imagen,
                   t.numero_talla,
                   s.id_sucursal, s.nombre_sucursal
            FROM inventario i 
            INNER JOIN modelo m ON i.id_modelo = m.id_modelo
            INNER JOIN talla t ON i.id_talla = t.id_talla
            INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
            WHERE TRIM(i.codigo_qr) = :qr";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([":qr" => $qr]);

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//  validación sucursal (NO rompe flujo con reset)
if (!empty($datos)) {

    $id_sucursal_usuario = $_SESSION['sucursal'];
    $id_sucursal_producto = $datos[0]['id_sucursal'];

    if ($id_sucursal_usuario != $id_sucursal_producto) {

        $_SESSION['error'] = "❌ No puedes vender productos de otra sucursal.";

        //  IMPORTANTE: limpiar estado sin QR
        header("Location: registrar_venta.php");
        exit();
    }
}

// tipos de venta
$sql_tipo = "SELECT id_tipo_venta, nombre_tipo_venta FROM tipo_venta";
$stmt_tipo = $conexion->query($sql_tipo);
$tipos = $stmt_tipo->fetchAll(PDO::FETCH_ASSOC);


$id_sucursal_usuario = $_SESSION['sucursal'];

$sql_sucursal = "SELECT nombre_sucursal FROM sucursal WHERE id_sucursal = :id";
$stmt_sucursal = $conexion->prepare($sql_sucursal);
$stmt_sucursal->execute([":id" => $id_sucursal_usuario]);

$sucursal_usuario = $stmt_sucursal->fetch(PDO::FETCH_ASSOC);

include('../includes/header.php');
include('../includes/sidebar.php');
?>

    <div class="main">
        <div class="container-fluid">
            
            <div class="col-md-6 mb-2">

                <h5 class="pb-3">Registro de ventas - <?= $sucursal_usuario['nombre_sucursal']; ?></h5>
                
                <form method="GET" id="formQR" class="d-flex gap-2 align-items-start pb-3">
                    <div class="flex-grow-1">
                        <input type="text" name="qr" class="form-control mb-2" placeholder="Escanea o escribe el QR" required>
                        <small class="qr-help">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>Formato:</strong> NombreModelo-Sucursal <br>
                            <span>Ejemplo:</span> Sport-Black-Planta o Deportivo-Local
                        </small>
                    </div>

                      <button type="submit" class="btn btn-primary btn-qr-action">Buscar</button>
                    
                    <button type="button" onclick="iniciarScanner()" class="btn btn-success btn-qr-action">
                        <i class="bi bi-qr-code-scan"></i>
                    </button>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <div id="reader"></div>

            </div>

            <?php if (!empty($datos)): ?>
            
            <div class="modelo-card mb-3">

                <!-- IMAGEN -->
                <div class="modelo-image">
                    <img src="<?= $datos[0]['imagen']; ?>">
                </div>

                <!-- INFO -->
                <div class="modelo-info">

                    <h4><?= $datos[0]['nombre_modelo']; ?></h4>

                    <p>
                        <i class="bi bi-shop"></i>
                        <?= $datos[0]['nombre_sucursal']; ?>
                    </p>

                </div>

            </div>


            <div class="card shadow-sm p-3">

                <form action="guardar_venta.php" method="POST">

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Talla</label>
                            <select name="id_inventario" id="inventario" class="form-select" required>
                                <option value="">Seleccione talla</option>
                                <?php foreach ($datos as $d): ?>
                                    <option value="<?= $d['id_inventario'] ?>">
                                        Talla <?= $d['numero_talla'] ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cantidad:</label>
                            <input type="number" name="txtCantidad" id="cantidad" class="form-control" placeholder="Digite la cantidad vendida"min="1" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo de venta</label>
                            <select name="id_tipo_venta" class="form-select" required>
                                <option value="">Seleccione tipo de venta</option>

                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?= $t['id_tipo_venta'] ?>">
                                    Venta <?= $t['nombre_tipo_venta'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mt-4 mb-3">
                            <label class="form-label">Cantidad disponible</label>
                            <input type="number" id="cantidadDisponible" class="form-control" disabled>
                        </div>

                        <div>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Registrar venta</button>
                        </div>
                    </div>
                </form>

            </div>
            
            <?php endif; ?>

            <?php if ($qr && empty($datos)): ?>
                <div class="alert alert-danger mt-2">
                    ❌ No se encontró producto con ese QR
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include('../includes/footer.php'); ?>

<!-- LIMPIAR URL QR (IMPORTANTE) -->
<script>
if (window.location.search.includes("qr=") || window.location.search.includes("reset")) {
    window.history.replaceState({}, document.title, "registrar_venta.php");
}
</script>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
function iniciarScanner() {

    const html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {

        if (!devices || devices.length === 0) {
            alert("No se detectaron cámaras");
            return;
        }

        // 🔥 Iniciar directamente con cámara trasera
        html5QrCode.start(
            { facingMode: "environment" }, // 👈 cámara trasera en móviles
            {
                fps: 10,
                qrbox: 250,
                aspectRatio: 1.0
            },
            (decodedText) => {

                // colocar QR en input
                const input = document.querySelector("input[name='qr']");
                input.value = decodedText;

                // detener cámara
                html5QrCode.stop().then(() => {
                    document.getElementById("reader").innerHTML = "";
                });

                // enviar formulario
                document.getElementById("formQR").submit();
            },
            (errorMessage) => {
                // errores de lectura silenciosos (no molestan)
                console.log(errorMessage);
            }
        );

    }).catch(err => {
        console.log(err);
        alert("Error al acceder a la cámara");
    });
}
</script>

<script>
const inventario = <?= json_encode($datos); ?>;
</script>

<script>
   const select = document.getElementById('inventario');

if (select) {
    select.addEventListener('change', function() {

        let id = this.value;

        let item = inventario.find(i => i.id_inventario == id);

        document.getElementById('cantidadDisponible').value = item 
            ? item.cantidad_disponible 
            : '';
    });
}
</script>

<style>
#reader {
    width: 100%;
    max-width: 350px;
    margin: auto;
}

.modelo-img {
    width: 150px;
    height: 130px;
    object-fit: cover;
    border-radius: 16px;
    margin: 10px auto;
    display: block;
}

.card {
    border-radius: 16px;
    max-width: 1500px;
}

/* MOBILE */
@media (max-width: 768px) {

    .modelo-img {
        width: 120px;
        height: 100px;
    }

    #reader {
        max-width: 100%;
    }
}

.modelo-card {
    display: flex;
    align-items: center;
    gap: 18px;

    background: #ffffff;
    padding: 18px 22px;
    border-radius: 18px;

    border: 1px solid #e5e7eb;

    position: relative;
    overflow: hidden;
    max-width: 1500px;
}

/* línea lateral elegante */
.modelo-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 15%;
    width: 5px;
    height: 70%;
    background: linear-gradient(180deg, #3b82f6, #1d4ed8);
    border-radius: 10px;
}

/* imagen */
.modelo-image img {
    width: 90px;
    height: 90px;
    object-fit: cover;

    border-radius: 14px;
    border: 2px solid #f1f5f9;
}

/* info */
.modelo-info h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
}

.modelo-info p {
    margin: 6px 0 0;
    font-size: 14px;
    color: #64748b;

    display: flex;
    align-items: center;
    gap: 6px;
}

.qr-help{
    display: inline-block;
    margin-top: 15px;
    padding: 10px 12px;

    background: #f8fafc;
    border: 1px solid #dbeafe;
    border-left: 4px solid #2563eb;

    border-radius: 10px;

    font-size: 13px;
    line-height: 1.5;
    color: #475569;

    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.qr-help i{
    color: #2563eb;
    margin-right: 5px;
}

.qr-help strong{
    color: #1e3a8a;
}

.qr-help span{
    color: #64748b;
    font-weight: 500;
}

.btn-qr-action{
    height: 45px;
    min-width: 55px;
    margin-top: 0;
    align-self: flex-start;
}

@media (max-width: 768px){

    #formQR{
        flex-wrap: wrap;
        gap: 10px;
    }

    #formQR .flex-grow-1{
        width: 100%;
    }

    .btn-qr-action{
        flex: 1;
        height: 48px;
        min-width: unset;
    }

    .qr-help{
        width: 100%;
        font-size: 12px;
        padding: 10px 12px;
    }
}
</style>


