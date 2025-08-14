<?php
// Incluye la configuración con la conexión a la base de datos
require_once 'admin/config.php';
// Incluye el nuevo header público
require_once 'admin/public_header.php';

// Consulta para obtener los tipos de invitación desde la base de datos
$resultado = mysqli_query($conn, "SELECT id, nombre, precio_base, url_imagen FROM tipos_invitacion ORDER BY precio_base ASC");
$tiposInvitacion = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diseños Ugalde - Invitaciones Digitales</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <main>
        <section class="hero">
            <h1>Invitaciones Digitales Personalizadas</h1>
            <p>"PORQUE TU FIESTA EMPIEZA, DESDE LA INVITACIÓN"</p>
            <a href="#paquetes" class="btn btn-primary">Ver Paquetes</a>
        </section>

        <section id="paquetes" class="product-section">
            <h2>Nuestras Invitaciones</h2>
            <div class="product-grid">
                <?php foreach ($tiposInvitacion as $paquete): ?>
                    <div class="product-card">
                        <?php 
                            $imagen_url = !empty($paquete['url_imagen']) 
                                ? 'uploads/invitaciones/' . htmlspecialchars($paquete['url_imagen']) 
                                : 'https://via.placeholder.com/300x200/764ba2/ffffff?text=' . urlencode($paquete['nombre']);
                        ?>
                        <img src="<?= $imagen_url ?>" alt="<?= htmlspecialchars($paquete['nombre']) ?>">
                        <h3><?= htmlspecialchars($paquete['nombre']) ?></h3>
                        <p class="price">$<?= number_format($paquete['precio_base'], 2) ?> MXN</p>
                        <a href="producto.php?id=<?= $paquete['id'] ?>" class="btn btn-secondary">Ver Detalles</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="portfolio-cta">
            <h2>Conoce Nuestro Trabajo</h2>
            <p>Mira los bocetos y diseños que hemos creado para clientes felices.</p>
            <a href="portafolio.php" class="btn btn-primary">Ver Bocetos</a>
        </section>
    </main>

    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> Diseños Ugalde. Todos los derechos reservados.</p>
    </footer>

</body>
</html>
