<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calzado Bernal</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="icon" type="image/jpeg" href="/image/logo.jpg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style.css?v=3">
</head>

<body>

    <header class="topbar">
        <div class="left d-flex align-items-center gap-2">
            <button id="menu-toggle" class="btn-menu d-lg-none">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <div class="right user-info" onclick="toggleMenu()">

            <img src="/image/usuarios/<?= $_SESSION['foto'] ?? 'default.png' ?>" class="user-img">

            <div class="user-text">
                <span class="user-name"><?= $_SESSION['nombre'] ?></span>
                <small class="user-role">
                    <?= ($_SESSION['rol'] == 1) ? 'Jefe' : 'Operario' ?>
                </small>
            </div>

            <div class="user-menu" id="userMenu">
                <a href="/login/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </header>



