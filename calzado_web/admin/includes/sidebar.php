<?php include("../config/conexion.php"); ?>

<?php
$sqlCategorias = "SELECT c.nombre_categoria, COUNT(p.id_producto) AS total
FROM categorias c
LEFT JOIN producto p
    ON c.id_categoria = p.id_categoria
GROUP BY c.id_categoria
";

$stmtCategorias = $conexion->query($sqlCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

$sqlTotal = "SELECT COUNT(*) FROM producto";
$totalProductos = $conexion->query($sqlTotal)->fetchColumn();
?>
<!-- SIDEBAR -->
    <aside id="sidebar">
        <div class="sidebar-header-mobile">

            <button id="btnCerrarSidebar" class="btn-cerrar-sidebar">
               <i class="bi bi-shop"></i>
            </button>

            <div class="sidebar-logo">
                <a href="productos.php">Admin.Web</a>
            </div>
        </div>

        <ul class="sidebar-nav">
            <div class="mb-2">
                <h6 class="mt-3">Secciones</h6>

                <li class="sidebar-item">
                    <a href="productos.php" class="sidebar-link"
                    data-filtro="todos">
                        Todos <span class="sidebar-count"><?=$totalProductos; ?></span>
                    </a>
                </li>
            </div>

            <hr class="sidebar-divider">

            <div class="mb-2">
                <h6>Categorías</h6>
                <?php foreach($categorias as $categoria): ?>

                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link filtro-link"
                        data-categoria="<?= $categoria['nombre_categoria']; ?>">
                            <span>
                                <?= $categoria['nombre_categoria']; ?>
                            </span>

                            <span class="sidebar-count">
                                <?= $categoria['total']; ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </div>

            <hr class="sidebar-divider">

            <div class="mb-2">
                <h6>Estado</h6>

                <li class="sidebar-item">
                    <a href="#" class="sidebar-link filtro-link"
                    data-estado="Nuevo">
                        Nuevo
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="#" class="sidebar-link filtro-link"
                    data-estado="Normal">
                        Normal
                    </a>
                </li>
            </div>
        </ul>

        <hr class="sidebar-divider">

    
        <a href="../../../dashboard/index.php" class="sidebar-link" data-title="Salir">
            <i class="bi bi-box-arrow-left"></i>
            <span>Salir</span>
        </a>
        

    </aside>
    
<style>
.sidebar-nav h6{
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #627b9e;
    padding-left: 14px;
}

.sidebar-divider{
    border: none;
    border-top: 1px solid #c8d5e2;
    opacity: 1;
}
.sidebar-link{
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sidebar-count{
    min-width: 30px;
    height: 30px;

    padding: 0 8px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #d3e3f4ec;
    color: #3966a9;

    font-size: 12px;
    font-weight: 600;

    border-radius: 50%;
}

.sidebar-link.active{
    background: #3962cc !important;
    color: #fff !important;
}

.sidebar-link{
    color: #14295c !important;
    font-size: 14px;
}

.sidebar-link.active .sidebar-count{
    background: rgba(255,255,255,.2);
    color: #fff;
}

#sidebar{
    width: 260px;
    height: 100vh;

    position: fixed;
    top: 0;
    left: 0;

    background: #dfeaf3;

    padding: 20px;
    display: flex;
    flex-direction: column;

    z-index: 1300;
}

/* SIDEBAR */
.sidebar-logo a {
    color: #0c26a9;
    font-size: 20px;
    font-weight: 700;
    text-decoration: none;

    letter-spacing: .5px;
}

/* HEADER SIDEBAR */
#sidebar .d-flex {
    align-items: center;
    margin-bottom: 25px;
}

/* NAV */
.sidebar-nav {
    list-style: none;
    flex: 1;
    padding: 0;
}

.sidebar-item {
    margin-bottom: 3px;
}

/* LINKS */
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;

    color: #010a0f;
    text-decoration: none;

    border-radius: 10px;

    font-size: 14px;
    font-weight: 500; /* 🔥 más elegante */
    letter-spacing: 0.3px;

    transition: all 0.25s ease;
}

.sidebar-link:hover {
    background: rgba(255,255,255,0.08);
    color: #031b13;
    transform: translateX(1px); /* pequeño movimiento */
}

.sidebar-link i {
    font-size: 18px;
}

/* DROPDOWN */
.sidebar-dropdown {
    padding-left: 20px;
}

.sidebar-dropdown .sidebar-link {
    font-size: 14px;
}

i {
    color: #1b3371; /* negro */
}

.sidebar-header-mobile{
    display:flex;
    align-items:center;
    gap:15px;
    justify-content:flex-start;
    margin-bottom:20px;
}

.sidebar-logo{
    flex:1;
}

.sidebar-logo a{
    display:block;
    margin-left:0;
}

.btn-cerrar-sidebar{
    display:none;
}

@media (max-width:768px){

    .btn-cerrar-sidebar{
        display:block;
    }

}

@media (max-width:768px){

    #sidebar{
        top: 0;
        left: -260px;

        width: 260px;
        height: 100vh;

        z-index: 1300;

        transition: .3s ease;
    }

    #sidebar.show{
        left: 0;
    }

}

.btn-cerrar-sidebar{
    width: 45px;
    height: 43px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: none;
    border-radius: 10px;

    background: #cad9eeea;
    color: #0c225a;

    cursor: pointer;
    transition: all .25s ease;
}

.btn-cerrar-sidebar i{
    font-size: 23px;
    color: #0c225a;
}

</style>

<?php include("footer.php"); ?>