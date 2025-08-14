<?php
require_once 'header.php';

// --- LÓGICA DE AUTO-BORRADO ---
// Borra las notificaciones que fueron leídas hace más de 24 horas
mysqli_query($conn, "DELETE FROM notificaciones WHERE leida = 1 AND fecha_leida < NOW() - INTERVAL 24 HOUR");

// --- LÓGICA PARA MARCAR TODAS COMO LEÍDAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_leidas'])) {
    mysqli_query($conn, "UPDATE notificaciones SET leida = 1, fecha_leida = NOW() WHERE leida = 0");
    header("Location: notificaciones.php");
    exit;
}

// --- OBTENER NOTIFICACIONES DE LA BASE DE DATOS ---
$res_nuevas = mysqli_query($conn, "SELECT * FROM notificaciones WHERE leida = 0 ORDER BY fecha_creacion DESC");
$notificaciones_nuevas = mysqli_fetch_all($res_nuevas, MYSQLI_ASSOC);

$res_leidas = mysqli_query($conn, "SELECT * FROM notificaciones WHERE leida = 1 ORDER BY fecha_creacion DESC LIMIT 50");
$notificaciones_leidas = mysqli_fetch_all($res_leidas, MYSQLI_ASSOC);
?>

<!-- Banner oculto para notificar nuevos pedidos -->
<div id="new-notification-banner" class="notification-banner" style="display: none;">
    Hay nuevos pedidos. <a href="notificaciones.php">Haz clic aquí para actualizar.</a>
</div>

<div id="notificaciones" class="tab-content active">
    
    <!-- SECCIÓN DE NOTIFICACIONES NUEVAS -->
    <div class="card">
        <div class="card-header">
            <h2>🔔 Nuevas Notificaciones (<span id="nuevas-count"><?= count($notificaciones_nuevas) ?></span>)</h2>
            <?php if (count($notificaciones_nuevas) > 0): ?>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="marcar_leidas" class="btn btn-secondary">Marcar todas como leídas</button>
                </form>
            <?php endif; ?>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mensaje</th>
                    <th style="width: 200px;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notificaciones_nuevas)): ?>
                    <tr>
                        <td colspan="2">No hay notificaciones nuevas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notificaciones_nuevas as $notificacion): ?>
                    <tr class="notification-row-unread">
                        <td><?= $notificacion['mensaje'] ?></td>
                        <td><?= date('d/m/Y h:i A', strtotime($notificacion['fecha_creacion'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SECCIÓN DE NOTIFICACIONES LEÍDAS -->
    <div class="card">
        <h2>✔️ Notificaciones Leídas</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mensaje</th>
                    <th style="width: 200px;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($notificaciones_leidas)): ?>
                    <tr>
                        <td colspan="2">No hay notificaciones leídas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notificaciones_leidas as $notificacion): ?>
                    <tr>
                        <td><?= $notificacion['mensaje'] ?></td>
                        <td><?= date('d/m/Y h:i A', strtotime($notificacion['fecha_creacion'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cada 15 segundos, revisa si hay nuevas notificaciones
    setInterval(function() {
        fetch('api_check_notificaciones.php')
            .then(response => response.json())
            .then(data => {
                const nuevasCountSpan = document.getElementById('nuevas-count');
                const currentCount = parseInt(nuevasCountSpan.textContent, 10);
                
                // Si el contador de la base de datos es mayor al que se muestra en la página,
                // significa que llegó un nuevo pedido.
                if (data.nuevas > currentCount) {
                    document.getElementById('new-notification-banner').style.display = 'block';
                }
            })
            .catch(error => console.error('Error al verificar notificaciones:', error));
    }, 15000); // 15000 milisegundos = 15 segundos
});
</script>

<?php include 'footer.php'; ?>