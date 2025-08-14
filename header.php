<?php 
require_once 'auth_check.php'; 
require_once 'config.php'; 

// Lógica para contar notificaciones no leídas
$notificaciones_no_leidas = 0;
$res_notif = mysqli_query($conn, "SELECT COUNT(id) AS total FROM notificaciones WHERE leida = 0");
if ($res_notif) {
    $notificaciones_no_leidas = mysqli_fetch_assoc($res_notif)['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DISEÑOS UGALDE - Admin</title>
    <link rel="stylesheet" href="../styles.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        .btn-public-gradient {
            background-image: linear-gradient(45deg, #8A2BE2, #4B0082); /* Degradado morado */
            color: white !important;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: transform 0.2s;
        }

        .btn-logout {
            background-color: #d9534f; /* Color rojo */
            color: white !important;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        /* Efecto hover para que los botones reaccionen al pasar el mouse */
        .btn-public-gradient:hover {
            transform: scale(1.05);
        }

        .btn-logout:hover {
            background-color: #c9302c; /* Rojo un poco más oscuro */
        }
    </style>
    </head>
<body>
    <div class="app-container">
        <header class="app-header">
            <h1>DISEÑOS UGALDE - ADMINISTRACIÓN</h1>
            <nav class="nav-tabs">
    
                <a href="../index.php" class="tab-link btn-public-gradient">🏠 Inicio Público</a>
                <a href="../portafolio.php" class="tab-link btn-public-gradient">🖼️ Portafolio Público</a>
                <p>
                <a href="pagos.php" class="tab-link">💳 Pagos</a>
                <a href="clientes.php" class="tab-link">👥 Clientes</a>
                <a href="pedidos.php" class="tab-link">💌 Pedidos</a>
                <a href="invitaciones.php" class="tab-link">🎨 Invitaciones</a>
                <a href="adicionales.php" class="tab-link">⭐ Adicionales</a>
                <a href="bocetos.php" class="tab-link">🖼️ Bocetos</a>
                <a href="notificaciones.php" class="tab-link notification-link">
                    🔔 Notificaciones 
                    <?php if ($notificaciones_no_leidas > 0): ?>
                        <span class="notification-badge"><?= $notificaciones_no_leidas ?></span>
                    <?php endif; ?>
                </a>
                
                <a href="logout.php" class="tab-link btn-logout">Cerrar Sesión</a>
            </nav>
        </header>
        <main class="app-content">