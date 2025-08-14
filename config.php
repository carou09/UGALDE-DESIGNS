<?php
// Credenciales de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'disenos');

// Crear la conexión
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecer el juego de caracteres
mysqli_set_charset($conn, "utf8mb4");

// --- CORRECCIÓN DE SESIÓN ---
// Inicia la sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
