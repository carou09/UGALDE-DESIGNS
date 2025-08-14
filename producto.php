<?php
require_once 'admin/config.php';

// Validar que el ID sea un número
$invitacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invitacion_id <= 0) {
    die("Invitación no encontrada.");
}

// Obtener los detalles de la invitación seleccionada
$stmt = mysqli_prepare($conn, "SELECT * FROM tipos_invitacion WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $invitacion_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$producto = mysqli_fetch_assoc($result);

if (!$producto) {
    die("Invitación no encontrada.");
}

// Obtener todos los servicios adicionales disponibles
$adicionales_res = mysqli_query($conn, "SELECT * FROM servicios_adicionales ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles: <?= htmlspecialchars($producto['nombre']) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="main-header">
        <div class="logo">Diseños Ugalde</div>
        <nav class="main-nav">
            <a href="index.php">Inicio</a>
            <a href="portafolio.php">Bocetos</a>
        </nav>
    </header>

    <main class="product-detail-container">
        <div class="product-image-area">
            <?php 
                $imagen_url = !empty($producto['url_imagen']) 
                    ? 'uploads/invitaciones/' . htmlspecialchars($producto['url_imagen']) 
                    : 'https://via.placeholder.com/500x350/764ba2/ffffff?text=' . urlencode($producto['nombre']);
            ?>
            <img src="<?= $imagen_url ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
        </div>
        
        <div class="product-info-area">
            <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
            <h2>¿Qué incluye?:</h2>
            <p><?= nl2br(htmlspecialchars($producto['caracteristicas'])) ?></p>

            <form action="procesar_pedido.php" method="POST">
                <input type="hidden" name="tipo_id" value="<?= $producto['id'] ?>">
                <!-- Campo oculto para enviar el precio final calculado -->
                <input type="hidden" name="precio_final" id="precio_final_hidden" value="<?= $producto['precio_base'] ?>">

                <h3>Nombre completo:</h3>
                <input type="text" name="cliente_nombre" placeholder="Escribe aqui tu nombre..." required>
                <h3>Correo electrónico:</h3>
                <input type="email" name="cliente_email" placeholder="Escribe aqui tu correo electrónico..." required>
                <h3>Número celular:</h3>
                <input type="tel" name="cliente_telefono" placeholder="Escribe aqui número de celular..." required>
                
                <!-- === CAMPO NUEVO AÑADIDO === -->
                <h3>Evento a realizar:</h3>
                <input type="text" name="nombre_evento" placeholder="Ej: Boda, XV Años, Bautizo..." required>

                <div class="extras-section">
                    <h3>Adicionales:</h3>
                    <div class="checklist-grid">
                        <?php while($extra = mysqli_fetch_assoc($adicionales_res)): ?>
                            <div class="checklist-item">
                                <input type="checkbox" class="adicional-checkbox" name="adicionales[]" value="<?= $extra['id'] ?>" data-precio="<?= $extra['precio'] ?>" id="extra-<?= $extra['id'] ?>">
                                <label for="extra-<?= $extra['id'] ?>"><?= htmlspecialchars($extra['nombre']) ?> (+ $<?= number_format($extra['precio'], 2) ?>)</label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="total-section">
                    <h3>Total: <span id="precio_total_display">$<?= number_format($producto['precio_base'], 2) ?> MXN</span></h3>
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">Realizar Pedido</button>
            </form>
        </div>
    </main>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const precioBase = <?= (float)$producto['precio_base'] ?>;
        const precioDisplay = document.getElementById('precio_total_display');
        const precioHiddenInput = document.getElementById('precio_final_hidden');
        const adicionalesCheckboxes = document.querySelectorAll('.adicional-checkbox');

        function calcularTotal() {
            let total = precioBase;
            adicionalesCheckboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    total += parseFloat(checkbox.getAttribute('data-precio')) || 0;
                }
            });
            // Actualiza tanto el texto visible como el campo oculto que se envía
            precioDisplay.textContent = '$' + total.toFixed(2) + ' MXN';
            precioHiddenInput.value = total.toFixed(2);
        }

        adicionalesCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', calcularTotal);
        });
    });
    </script>
</body>
</html>