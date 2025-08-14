<?php
// Incluye la configuración para conectar a la base de datos y verificar la sesión
require_once 'auth_check.php';
require_once 'config.php';

// Verifica que los datos se hayan enviado mediante el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recoge y convierte los datos del formulario de forma segura
    $pedido_id = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : 0;
    $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
    $metodo = $_POST['metodo'] ?? 'No especificado';

    // Valida que los datos sean correctos antes de insertarlos
    if ($pedido_id > 0 && $monto > 0) {
        
        // Prepara la consulta SQL para evitar inyecciones SQL
        $stmt = mysqli_prepare($conn, "INSERT INTO pagos (pedido_id, monto, metodo, fecha_pago) VALUES (?, ?, ?, NOW())");
        
        // Vincula las variables a la consulta preparada
        mysqli_stmt_bind_param($stmt, "ids", $pedido_id, $monto, $metodo);
        
        // Ejecuta la consulta para guardar el pago
        mysqli_stmt_execute($stmt);
    }

    // Redirige al usuario de vuelta a la página desde la que vino.
    // Esto permite que el mismo script funcione para el modal en 'pagos.php' y 'ver_pedido.php'.
    if (isset($_POST['source']) && $_POST['source'] == 'ver_pedido') {
        header("Location: ver_pedido.php?id=" . $pedido_id);
    } else {
        header("Location: pagos.php");
    }
    exit;
} else {
    // Si alguien intenta acceder a este archivo directamente, lo redirige
    header("Location: pagos.php");
    exit;
}
?>