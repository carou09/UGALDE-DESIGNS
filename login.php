<?php
// Incluye la configuración de la base de datos
require_once 'config.php';
$error = '';

// Si el método es POST, intenta iniciar sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepara la consulta para buscar al administrador de forma segura
    $stmt = mysqli_prepare($conn, "SELECT password FROM administradores WHERE username = ?");
    
    // LÍNEA CORREGIDA: Ahora usa la variable $username que viene del formulario
    mysqli_stmt_bind_param($stmt, "s", $username); 
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Verifica que la contraseña ingresada coincida con la guardada en la base de datos
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header('Location: pagos.php'); // Redirige al dashboard del admin
            exit;
        }
    }
    
    // Si algo falla, muestra un error
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
    <!-- Sube un nivel para encontrar el archivo de estilos -->
    <link rel="stylesheet" href="../styles.css">
    
    <!-- Estilos para centrar el formulario en esta página específica -->
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 5rem 2rem;
        }
    </style>
</head>
<body>

    <!-- Encabezado de la página pública -->
    <header class="main-header">
        <div class="logo">Diseños Ugalde</div>
        <nav class="main-nav">
            <a href="../index.php">Inicio</a>
            <a href="../portafolio.php">Portafolio</a>
        </nav>
    </header>

    <!-- Contenedor principal para el formulario de login -->
    <main class="login-container">
        <div class="card" style="width: 350px;">
            <h2>Inicio de sesión:</h2>
            <form method="POST" action="login.php">
                <input type="text" name="username" placeholder="Usuario" required style="margin-bottom: 1rem;">
                <input type="password" name="password" placeholder="Contraseña" required style="margin-bottom: 1rem;">
                
                <?php if($error): ?>
                    <p style="color:red; text-align:center;"><?= $error ?></p>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary" style="width:100%;">Entrar</button>
            </form>
        </div>
    </main>

</body>
</html>