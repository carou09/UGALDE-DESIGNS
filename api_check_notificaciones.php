<?php
// Incluye la configuración de la base de datos y el auth_check
require_once 'auth_check.php';
require_once 'config.php';

// Consulta para contar cuántas notificaciones no leídas hay
$res_notif = mysqli_query($conn, "SELECT COUNT(id) AS total FROM notificaciones WHERE leida = 0");
$nuevas_count = 0;
if ($res_notif) {
    $nuevas_count = (int)mysqli_fetch_assoc($res_notif)['total'];
}

// Devuelve el resultado en formato JSON para que JavaScript pueda leerlo
header('Content-Type: application/json');
echo json_encode(['nuevas' => $nuevas_count]);
exit;
?>
