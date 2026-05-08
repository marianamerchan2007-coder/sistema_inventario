<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calzado Bernal</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="right-panel">
        <div class="login-box">
            <form action="login.php" method="POST">

                <div class="logo">
                    <img src="../image/logo.jpg" alt="">
                </div>

                <h2>Bienvenido</h2>
                <small style="text-center">Sistema de inventario y ventas</small>
                <span class="login-subtitle">Iniciar sesión: </span>

                <?php if (isset($_SESSION['error_login'])): ?>
                    <div class="error-box">
                        <?= $_SESSION['error_login']; ?>
                    </div>
                    <?php unset($_SESSION['error_login']); ?>
                <?php endif; ?>

                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nombre_usuario" placeholder="Usuario" autocomplete="off" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="contrasenia" placeholder="Contraseña" autocomplete="new-password" required>
                </div>

                <button type="submit">Ingresar</button>

            </form>
        </div>
    </div>

</div>

</body>
</html>

