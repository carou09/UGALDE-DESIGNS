<?php
require_once 'config.php'; // Incluye la conexión a la BD

// Actualiza las notificaciones no leídas para marcarlas como leídas
// y registra la fecha y hora en que se leyeron.
$stmt = mysqli_prepare($conn, "UPDATE notificaciones SET leida = 1, fecha_leida = NOW() WHERE leida = 0");
mysqli_stmt_execute($stmt);

// Responde con éxito para que el JavaScript sepa que funcionó
http_response_code(200);
echo json_encode(['status' => 'success']);
?>
