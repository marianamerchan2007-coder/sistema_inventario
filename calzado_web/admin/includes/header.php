<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin calzado Bernal</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="/calzado_web/style.css?v=<?= time(); ?>">
</head>
<body>
    
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button id="btnSidebar" class="btn-sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <nav class="topbar-nav">
            <a href="/calzado_web/admin/productos.php">Productos</a>
            <a href="/calzado_web/admin/configuracion.php">Contacto</a>
            <a href="#">Sitio Web</a>
        </nav>
    </header>

    <div id="overlay"></div>

<style>
/* TOPBAR */
.topbar {
    position: fixed;
    top: 0;
    left: 260px;
    width: calc(100% - 260px);
    height: 70px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 25px;

    background: #fff;
    border-bottom: 1px solid #ddd;

    z-index: 1200;
}

/* NAV */
.topbar-nav {
    display: flex;
    gap: 20px;
}

.topbar-nav a{
    text-decoration: none;
    padding: 6px 12px;
    font-size: 15px;
    font-weight: 500;
    border-radius: 10px;
    background: #dbeaffdb;
    color: #090964ec;
}

/* Botón menú oculto en escritorio */
.btn-sidebar{
    display: none;
}

/* Mostrar solo en móviles */
@media (max-width: 768px){

    .btn-sidebar{
        display: flex;
        align-items: center;
        justify-content: center;

        width: 40px;
        height: 40px;

        border: 1px solid #ddd;
        border-radius: 10px;

        background: #fff;
        cursor: pointer;
    }

    .btn-sidebar i{
        font-size: 23px;
    }

    .topbar{
        left: 0;
        width: 100%;
    }

    .topbar-nav{
        display: flex;
        gap: 6px; /* antes 8px */
    }

    .topbar-nav a{
        padding: 4px 8px;   
        font-size: 13px;   
        border-radius: 6px; 
        background: #f5f7fa;
        white-space: nowrap; 
    }

    #sidebar{
        left: -260px;
        transition: .3s ease;
    }

    #sidebar.show{
        left: 0;
    }

    #overlay{
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.35);

        opacity: 0;
        visibility: hidden;

        transition: .3s ease;
        z-index: 1290;
    }

    #overlay.show{
        opacity: 1;
        visibility: visible;
    }
}
</style>
