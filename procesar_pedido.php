<?php
// Carga la configuración con la conexión a la BD
require_once 'admin/config.php';

// --- 1. Recibir y Validar Datos del Formulario ---
$cliente_nombre = $_POST['cliente_nombre'] ?? 'Cliente Web';
$cliente_email = $_POST['cliente_email'] ?? '';
$cliente_telefono = $_POST['cliente_telefono'] ?? '';
$tipo_id = (int)($_POST['tipo_id'] ?? 0); 

// === VARIABLES CORREGIDAS Y NUEVAS ===
$nombre_evento = $_POST['nombre_evento'] ?? 'Evento desde la web'; // Se recibe el nombre del evento
$precio_final = (float)($_POST['precio_final'] ?? 0.00); // Se recibe el precio final calculado
$adicionales_ids = $_POST['adicionales'] ?? []; // Se recibe el array de adicionales

// --- 2. Crear o Encontrar al Cliente en la Base de Datos ---
$stmt = mysqli_prepare($conn, "SELECT id FROM clientes WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $cliente_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($cliente_existente = mysqli_fetch_assoc($result)) {
    $clienteId = $cliente_existente['id'];
} else {
    $stmt_insert = mysqli_prepare($conn, "INSERT INTO clientes (nombre, email, telefono) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert, "sss", $cliente_nombre, $cliente_email, $cliente_telefono);
    mysqli_stmt_execute($stmt_insert);
    $clienteId = mysqli_insert_id($conn);
}

// --- 3. Crear el Pedido en la Base de Datos ---
$stmt_pedido = mysqli_prepare($conn, "INSERT INTO pedidos (cliente_id, tipo_id, nombre_evento, fecha_evento, hora_evento, precio_final) VALUES (?, ?, ?, ?, ?, ?)");
$fecha_evento = date('Y-m-d'); // Fecha por defecto, puedes cambiarla si la pides en el formulario
$hora_evento = '12:00:00'; // Hora por defecto
mysqli_stmt_bind_param($stmt_pedido, "iisssd", $clienteId, $tipo_id, $nombre_evento, $fecha_evento, $hora_evento, $precio_final);
mysqli_stmt_execute($stmt_pedido);
$pedidoId = mysqli_insert_id($conn); // Obtenemos el ID del nuevo pedido

// === LÓGICA AÑADIDA PARA GUARDAR ADICIONALES ===
if (!empty($adicionales_ids) && $pedidoId > 0) {
    $stmt_adicional = mysqli_prepare($conn, "INSERT INTO pedido_servicios (pedido_id, servicio_id) VALUES (?, ?)");
    foreach ($adicionales_ids as $servicio_id) {
        $servicio_id_int = (int)$servicio_id;
        mysqli_stmt_bind_param($stmt_adicional, "ii", $pedidoId, $servicio_id_int);
        mysqli_stmt_execute($stmt_adicional);
    }
}

// --- 4. Crear la Notificación en la Base de Datos ---
$stmt_tipo_nombre = mysqli_prepare($conn, "SELECT nombre FROM tipos_invitacion WHERE id = ?");
mysqli_stmt_bind_param($stmt_tipo_nombre, "i", $tipo_id);
mysqli_stmt_execute($stmt_tipo_nombre);
$res_tipo_nombre = mysqli_stmt_get_result($stmt_tipo_nombre);
$tipo_nombre = mysqli_fetch_assoc($res_tipo_nombre)['nombre'] ?? 'Invitación';

$mensaje = "Nuevo pedido de <b>{$cliente_nombre}</b> para el evento <b>{$nombre_evento}</b>.";
$link = "ver_pedido.php?id=" . $pedidoId; // Link directo al resumen del nuevo pedido
$stmt_notif = mysqli_prepare($conn, "INSERT INTO notificaciones (mensaje, link) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt_notif, "ss", $mensaje, $link);
mysqli_stmt_execute($stmt_notif);

// --- 5. Redirigir a una página de agradecimiento ---
header('Location: gracias.php');
exit;
?>
