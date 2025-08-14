<?php
// Inicia la sesión de forma segura
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="logo">Diseños Ugalde</div>
        <nav class="main-nav">
            <a href="index.php">Inicio</a>
            <a href="portafolio.php">Bocetos</a>
            
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="admin/pedidos.php" class="btn btn-secondary">Panel</a>
                <a href="admin/logout.php" class="btn btn-login">Cerrar Sesión</a>
            <?php else: ?>
                <a href="admin/login.php" class="btn btn-login">Inicio de sesión</a>
            <?php endif; ?>
        </nav>
    </header>
