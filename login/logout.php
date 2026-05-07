<?php
session_start();

// destruir sesión
session_destroy();

// redirigir al login
header("Location: index.php");
exit;