<?php
include("../config/conexion.php");


$sql_count = "SELECT COUNT(*) as total FROM transferencias WHERE estado = 'pendiente'";
$stmt = $conexion->query($sql_count);
$totalPendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
    <!-- SIDEBAR -->
    <aside id="sidebar">
        <div class="d-flex align-items-center gap-5 mb-3">
            <div class="sidebar-logo">
                <a href="/dashboard/index.php">Calzado Bernal</a>
            </div>

            <button id="btn-close-sidebar" class="btn-close-sidebar d-lg-none">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <ul class="sidebar-nav">
           
            <li class="sidebar-item">
                <a href="/dashboard/index.php" class="sidebar-link" data-title="Dashboard">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($_SESSION['rol'] == 1): ?>
                <li class="sidebar-item">
                    <a href="/modelos/index.php" class="sidebar-link" data-title="Modelos">
                        <i class="bi bi-tag"></i>
                        <span>Modelos</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="sidebar-item">
                <a href="/inventario/index.php" class="sidebar-link" data-title="Inventario">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventario</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="/ventas/index.php" class="sidebar-link" data-title="Ventas">
                    <i class="bi bi-cash-stack"></i>
                    <span>Ventas</span>
                </a>
            </li>

            <?php if (
                $_SESSION['rol'] == 1 || 
                ($_SESSION['rol'] == 2 && $_SESSION['sucursal'] == 2)
            ): ?>

            <li class="sidebar-item">
                <a class="sidebar-link has-dropdown collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#transferencias" data-title="Transferencias">
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Transferencias</span>
                </a>

                <ul id="transferencias" class="sidebar-dropdown list-unstyled collapse">
                    <?php if ($_SESSION['rol'] == 2 && $_SESSION['sucursal'] == 2): ?>
                        <li class="sidebar-item">
                            <a href="/transferencias/transferencias_pendientes.php" class="sidebar-link" data-title="Pendientes">
                                Pendientes 
                                <?php if ($totalPendientes > 0): ?>
                                    <span class="badge bg-danger ms-2"><?= $totalPendientes ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($_SESSION['rol'] == 1): ?>
                        <li class="sidebar-item">
                            <a href="/transferencias/index.php" class="sidebar-link" data-title="Confirmadas">
                                Confirmadas
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>


            <?php if ($_SESSION['rol'] == 1): ?>
                <li class="sidebar-item">
                    <a class="sidebar-link has-dropdown collapsed"
                    data-bs-toggle="collapse"
                    data-bs-target="#reportes" data-title="Reportes">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Reportes</span>
                    </a>

                    <ul id="reportes" class="sidebar-dropdown list-unstyled collapse">
                        <li class="sidebar-item"><a href="/reportes/inventario.php" class="sidebar-link" data-title="Inventario">Inventario</a></li>
                        <li class="sidebar-item"><a href="/reportes/ventas.php" class="sidebar-link" data-title="Ventas">Ventas</a></li>
                        <li class="sidebar-item"><a href="/reportes/transferencias.php" class="sidebar-link" data-title="Transferencias">Transferencias</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="sidebar-item">
                <a href="/calzado_web/index.php" class="sidebar-link" data-title="Admin">
                    <i class="bi bi-gear"></i>
                    <span>Administrador Web</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="/login/logout.php" class="sidebar-link" data-title="Salir">
                <i class="bi bi-box-arrow-left"></i>
                <span>Salir</span>
            </a>
        </div>

    </aside>
    
<div id="sidebar-overlay"></div>




