<?php
session_start();
include("../config/conexion.php");
include("../includes/auth.php");

// datos del usuario logueado
$id_rol = $_SESSION['rol'];
$id_sucursal = $_SESSION['sucursal'];
?>

<?php include('../includes/header.php'); ?>
<?php include('../includes/sidebar.php'); ?>

<?php
$buscar = $_GET['buscar'] ?? '';

$sql = "SELECT 
    m.id_modelo,
    s.id_sucursal,
    m.nombre_modelo,
    m.imagen,
    m.origen_producto,
    s.nombre_sucursal,
    SUM(i.cantidad_disponible) AS cantidad_total,
    CONCAT(m.nombre_modelo, '-', s.nombre_sucursal) AS codigo_qr
FROM inventario i
INNER JOIN modelo m ON i.id_modelo = m.id_modelo
INNER JOIN sucursal s ON i.id_sucursal = s.id_sucursal
WHERE 1=1";

if (!empty($buscar)) {
    $sql .= " AND (m.nombre_modelo LIKE :buscar OR s.nombre_sucursal LIKE :buscar)";
}

if ($id_rol == 2) {
    $sql .= " AND i.id_sucursal = :sucursal";
}

$sql .= " GROUP BY 
    m.id_modelo,
    s.id_sucursal,
    m.nombre_modelo,
    s.nombre_sucursal";

$stmt = $conexion->prepare($sql);

$params = [];

if (!empty($buscar)) {
    $params[':buscar'] = "%$buscar%";
}

if ($id_rol == 2) {
    $params[':sucursal'] = $id_sucursal;
}

$stmt->execute($params);
$modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);


//nombre de sucursal
$sql_sucursal = "SELECT nombre_sucursal FROM sucursal WHERE id_sucursal = ?";
$stmt = $conexion->prepare($sql_sucursal);
$stmt->execute([$id_sucursal]);

$sucursal_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_sucursal = $sucursal_usuario['nombre_sucursal'] ?? '';
?>


    <div class="main">
        <div class="container-fluid">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                    <h5 class="mb-2 mb-md-0"> Inventario <?php if ($id_rol == 2): ?>
                            - <?= $nombre_sucursal ?>
                        <?php endif; ?>
                    </h5>
                
                    <div class="d-flex flex-column flex-sm-row gap-2 ms-md-auto">

                        <form method="GET" class="search-box d-flex">
                            <button class="btn btn-primary btn-sm">
                                <i class="bi bi-search"></i>
                            </button>

                            <input type="text" name="buscar" class="form-control form-control-sm"
                                placeholder="Buscar por modelo o sucursal">
                        </form>

                        <a href="nuevo_registro.php" class="btn btn-primary btn-nuevo">+ Nuevo registro</a>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i>
                        <?= $_SESSION['success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if ($buscar != '' && empty($modelos)): ?>
                    <div class="alert alert-warning">
                        No se encontraron registros de 
                        <strong><?= htmlspecialchars($buscar) ?></strong>
                    </div>
                <?php endif; ?>

                <div class="table-responsive d-none d-md-block">
                    <table class="table align-middle text-center">
                        <thead>
                            <tr>
                               
                                <th>QR / Descargar</th>
                                <th>Imagen</th>
                                <th>Modelo</th>
                                <th>Cantidad disponible</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($modelos as $modelo): ?>
                                <tr>
                                    <td>
                                        <!-- Ver QR -->
                                        <button class="btn btn-qr1 btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#qrModal<?= $modelo['id_modelo'] . '_' . $modelo['id_sucursal'] ?>">
                                            <i class="bi bi-qr-code"></i>
                                            
                                        </button>

                                        <!-- E IMPRIMIR -->
                                        <button class="btn btn-print1 btn-sm"
                                                onclick="descargarQR(
                                                '<?= $modelo['id_modelo'] ?>',
                                                '<?= $modelo['id_sucursal'] ?>',
                                                '<?= $modelo['nombre_modelo'] ?>',
                                                '<?= $modelo['nombre_sucursal'] ?>'
                                            )">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    </td>
                                    
                                    <td>
                                        <img src="../image/<?php echo $modelo['imagen']; ?>"
                                        width="100" height="80"
                                        style="object-fit: cover; border-radius: 10px;"
                                        onclick="verImagen(this)">
                                    </td>

                                    <td>
                                        <strong class="d-block"><?= $modelo['nombre_modelo']; ?></strong>
                                        <?php if ($id_rol == 1): ?>
                                            <small class="text-muted">
                                                <?= $modelo['nombre_sucursal']; ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">
                                                <?= $modelo['origen_producto']; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary fs-7 px-3 py-2">
                                            <?= $modelo['cantidad_total']; ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <a href="registro_tallas.php?id=<?= $modelo['id_modelo']; ?>&sucursal=<?= $modelo['id_sucursal']; ?>" 
                                            class="btn btn-editar btn-sm">
                                            Ver tallas <i class="bi bi-arrow-right-short"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ===== MOBILE (CARDS INVENTARIO) ===== -->
            <div class="d-md-none">
                <?php foreach ($modelos as $modelo): ?>
                    
                    <div class="inventario-card">

                        <!-- IMAGEN -->
                        <div class="inventario-img-container">
                            <img src="../image/<?php echo $modelo['imagen']; ?>" class="inventario-img">
                        </div>

                        <!-- INFO -->
                        <div class="inventario-content">

                            <!-- NOMBRE -->
                            <div class="inventario-header">
    
                                <div class="inventario-title">
                                    <h6><?= $modelo['nombre_modelo']; ?></h6>

                                    <?php if ($id_rol == 1): ?>
                                        <span class="sucursal-chip">
                                            <?= $modelo['nombre_sucursal']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <span class="badge stock-badge">
                                    <?= $modelo['cantidad_total']; ?>
                                </span>

                            </div>

                            <!-- ACCIONES -->
                            <div class="inventario-actions">
                                
                                <button class="btn btn-qr"
                                    data-bs-toggle="modal"
                                    data-bs-target="#qrModal<?= $modelo['id_modelo'].'_'.$modelo['id_sucursal'] ?>">
                                    <i class="bi bi-qr-code"></i>
                                </button>

                                <button class="btn btn-print"
                                    onclick="descargarQR(
                                        <?= $modelo['id_modelo']; ?>,
                                        <?= $modelo['id_sucursal']; ?>,
                                        '<?= $modelo['nombre_modelo']; ?>',
                                        '<?= $modelo['nombre_sucursal']; ?>'
                                    )">
                                    <i class="bi bi-printer"></i>
                                </button>

                                <a href="registro_tallas.php?id=<?= $modelo['id_modelo']; ?>&sucursal=<?= $modelo['id_sucursal']; ?>" 
                                class="btn btn-tallas btn-sm">
                                    Ver tallas
                                </a>

                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>
                </div>
        </div>
    </div>

<!-- MODAL -->
                            <?php foreach ($modelos as $modelo): ?>
                                <div class="modal fade" id="qrModal<?= $modelo['id_modelo'] . '_' . $modelo['id_sucursal'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content qr-modal">

                                            <div class="modal-header qr-header">
                                                <h5 class="modal-title"><i class="bi bi-qr-code me-2"></i> Código qr</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body text-center">

                                                <div class="qr-container">
                                                    <!-- QR -->
                                                    <img class="img-fluid"
                                                        id="qr-img-<?= $modelo['id_modelo'] . '_' . $modelo['id_sucursal'] ?>"
                                                        src="https://api.qrserver.com/v1/create-qr-code/?size=450x450&ecc=H&margin=30&color=0-0-139&bgcolor=255-255-255&data=<?= urlencode($modelo['codigo_qr']) ?>">

                                                    <!-- LOGO -->
                                                    <img src="../image/logo.jpg" class="qr-logo">
                                                </div>

                                                <!-- TEXTO ABAJO -->
                                                <div class="qr-text mt-3">
                                                    <div class="qr-title">
                                                        <?= $modelo['nombre_modelo'] ?>
                                                    </div>
                                                    <div class="qr-subtitle">
                                                        <?= $modelo['nombre_sucursal'] ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

<?php include('../includes/footer.php'); ?>

    <script>
        function descargarQR(modelo, sucursal) {

        const img = document.getElementById("qr-img-" + modelo + "_" + sucursal);

        if (!img) return;

        const nombreArchivo = `${modelo}-${sucursal}.png`;
        
        fetch(img.src)
            .then(r => r.blob())
            .then(blob => {

                const url = URL.createObjectURL(blob);

                const a = document.createElement("a");
                a.href = url;
                a.download = nombreArchivo;

                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                URL.revokeObjectURL(url);
            });
        }
</script>

<script>
 function descargarQR(idModelo, idSucursal, modelo, sucursal) {

    const img = document.getElementById("qr-img-" + idModelo + "_" + idSucursal);
    if (!img) return;

    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");

    const qrSize = 450;
    const padding = 20;
    const scale = 2;

    // Tamaño real (HD)
    canvas.width = 510 * scale;
    canvas.height = 620 * scale;

    // Escalado para nitidez
    ctx.scale(scale, scale);

    // Centro REAL (sin usar canvas.width)
    const centerX = 510 / 2;

    const qr = new Image();
    qr.crossOrigin = "anonymous";
    qr.src = img.src;

    qr.onload = function () {

        // 🔵 Fondo
        ctx.fillStyle = "#edf0f5";
        ctx.fillRect(0, 0, 510, 620);

        // ⚪ Tarjeta
        ctx.fillStyle = "#ffffff";
        ctx.beginPath();
        ctx.roundRect(20, 20, 470, 580, 25);
        ctx.fill();

        // 📦 QR (nítido)
        ctx.drawImage(qr, padding, padding, qrSize, qrSize);

        // 🧠 Texto
        ctx.textAlign = "center";

        // Modelo
        ctx.fillStyle = "#0d4394";
        ctx.font = "bold 18px Arial";
        ctx.fillText(modelo, centerX, 510);

        // Línea decorativa
        ctx.fillRect(centerX - 25, 525, 50, 3);

        // Sucursal
        ctx.fillStyle = "#6c757d";
        ctx.font = "14px Arial";
        ctx.fillText(sucursal, centerX, 550);

        // 📥 Descargar
        const link = document.createElement("a");
        link.download = `${modelo}-${sucursal}`
            .replace(/[^a-zA-Z0-9-_]/g, "_");

        link.href = canvas.toDataURL("image/png");
        link.click();
    };
}
</script>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
/*BOTONES*/
.btn-success {
    background-color: #87a6cd !important;
    color: rgb(36, 25, 97);
    font-weight: 500;
}
.bg-info{
    background-color: #3064ad !important;
     color: rgb(227, 226, 234);
     height: 1.5rem;
}
.bg-primary{
    background: #eef2ff !important;
    color: #3730a3;
    font-size: 13px;
}
.btn-qr1,
.btn-print1 {
    width: 40px;
    height: 40px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;
    border: 1px solid transparent;

    font-size: 18px;
    color: #1e293b;

    transition: all 0.25s ease;
}

.btn-qr1 {
    background: #e6f9ec;
    color: #16a34a;
}

.btn-qr1:hover {
    background: #87de99;
    color: #fff;
}

/* PRINT */
.btn-print1 {
    background: #e6f4fb;
    color: #0284c7;
}

.btn-print1:hover {
    background: #90d4f7;
    color: #fff;
}
.btn-qr,
.btn-print {
    width: 30px;
    height: 30px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;
    border: 1px solid transparent;

    font-size: 18px;
    color: #1e293b;

    transition: all 0.25s ease;
}

/* QR */
.btn-qr {
    background: #e6f9ec;
    color: #16a34a;
}

.btn-qr:hover {
    background: #87de99;
    color: #fff;
}

/* PRINT */
.btn-print {
    background: #e6f4fb;
    color: #0284c7;
}

.btn-print:hover {
    background: #90d4f7;
    color: #fff;
}

.btn-editar {
    background: #2866d2;
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
    background: #2a61ae;
    color: #e9ebf1;
    transform: translateY(-1px);
}

.qr-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

/* Contenedor del modal */
.qr-modal {
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
}

/* Imagen QR */
.qr-img {
    width: 100%;
    max-width: 250px;
    border-radius: 15px;
}

/* Logo centrado */
.qr-logo {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    width: 30%;   /* 👈 relativo al QR */
    height: 30%;

    border-radius: 50%;
    object-fit: cover;
}

/* Texto inferior */
.qr-text {
    text-align: center;
    margin-top: 5px;
}

/* Nombre principal */
.qr-title {
    font-size: 18px;
    font-weight: 700;
    color: #212529;
    letter-spacing: 0.5px;
}

/* Sucursal */
.qr-subtitle {
    font-size: 15px;
    color: #4d5154;
    margin-top: 4px;
}

/* Opcional: efecto elegante */
.qr-title::after {
    content: "";
    display: block;
    width: 50px;
    height: 3px;
    background: #0d6efd;
    margin: 8px auto 0;
    border-radius: 5px;
}

.qr-header {
    background: #0d4394;
    color: white;
    border-bottom: none;
    padding: 15px 20px;
}

/* Título */
.qr-header .modal-title {
    font-weight: 600;
    display: flex;
    align-items: center;
    color: white;

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
    background: #eef2ff;
    color: #3730a3;
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