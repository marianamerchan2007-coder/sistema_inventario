<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Calzado Bernal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="panel">
        <h2>Bienvenido</h2>
        <small>Administrador del Sitio Web Calzado Bernal</small>
        <p>Desde aquí podrá actualizar la información de productos, imágenes, precios y demás contenidos publicados en el sitio web.</p>

        <form action="login.php" method="POST">

            <small>Ingrese su contraseña para continuar.</small>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="contrasenia" placeholder="Contraseña" required>
            </div>

            <div class="my-2">
                <?php if (isset($_SESSION['error_login'])): ?>
                    <div class="error-box">
                        <?= $_SESSION['error_login']; ?>
                    </div>
                <?php unset($_SESSION['error_login']); ?>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</body>
</html>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:25px;

    background:
        linear-gradient(#eef5ff, #f4f6f9);

    background-image:
        linear-gradient(rgba(32, 116, 218, 0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(14,111,228,.05) 1px, transparent 1px);

    background-size:40px 40px;
}

.panel{
    width:100%;
    max-width:550px;

    position:relative;
    z-index:1;
}

.panel h2{
    text-align:center;

    font-size:52px;
    font-weight:800;

    color: #2e43b6f0;

    letter-spacing:-1px;

    margin-bottom:8px;
}

.panel > small{
    display:block;

    text-align:center;

    font-size:18px;
    font-weight:600;

    color: #232fdebb;

    margin-bottom:24px;
}

.panel p{
   text-align:center;

    font-size:16px;
    line-height:1.8;

    color: #6b819f;

    margin-bottom:40px;
}

form small{
    display:block;

    font-size:14px;
    font-weight:500;

    color: #5b708d;

    margin-bottom:12px;
}

.input-group{
    position: relative;
    margin-bottom: 22px;
}

.input-group i{
    position: absolute;
    left: 18px;
    top: 50%;

    transform: translateY(-50%);

    font-size: 19px;
    color: #ccd3dc;

    z-index: 2;

    transition: .25s ease;
}

.input-group input{
    width: 100%;
    height: 60px;

    padding: 0 18px 0 52px;

    border: 1px solid #d7e1ec;
    border-radius: 16px;

    background: rgba(255,255,255,.95);

    font-size: 15px;
    font-weight: 500;
    color: #334155;

    outline: none;

    transition: all .25s ease;
}

.input-group input::placeholder{
    color: #94a3b8;
}

.input-group input:focus{
    border-color: #0e6fe4;

    background: #fff;

    box-shadow:
        0 0 0 5px rgba(14,111,228,.12),
        0 8px 20px rgba(14,111,228,.08);
}

.input-group:focus-within i{
    color: #3046c5;
    transform: translateY(-50%) scale(1.08);
}

.btn-primary{
    height:58px;

    border:none !important;
    border-radius:14px;

    background: #134fdbdf !important;

    font-size:15px;
    font-weight:600;

    transition:.25s ease;
}

.btn-primary:hover{
    background: #1d5be9 !important;

    transform:translateY(-1px);
}

.error-box{
    margin-bottom:20px;
    font-weight: 500;
    font-size: 15px;
    color: #dc2929;
}

@media(max-width:768px){

    .panel h2{
        font-size:33px;
    }

    .panel p{
        font-size:14px;
    }

    .panel > small{
        font-size:13px;
    }

    form small{
        font-size:13px;
        margin-bottom:10px;
    }

    .input-group input{
        height:52px;
        font-size:14px;
    }

    .input-group i{
        font-size:16px;
        left:16px;
    }

    .btn-primary{
        height:48px;
        font-size:14px;
        font-weight:500;
    }

    .error-box{
        font-size: 14px;
    }
}
</style>

