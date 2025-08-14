<?php
require_once 'header.php';

// --- LÓGICA PARA MARCAR COMO ENTREGADO ---
if (isset($_GET['action']) && $_GET['action'] == 'entregar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "UPDATE pedidos SET entregado = 1, fecha_entrega_real = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: pedidos.php");
    exit;
}

// --- LÓGICA PARA CARGAR DATOS AL EDITAR UN PEDIDO ---
// Inicializamos las variables para evitar el warning "Undefined variable"
$pedido_a_editar = null;
$adicionales_del_pedido = [];

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_a_editar = (int)$_GET['id'];
    
    // Obtenemos los datos principales del pedido
    $stmt_edit = mysqli_prepare($conn, "SELECT * FROM pedidos WHERE id = ?");
    mysqli_stmt_bind_param($stmt_edit, "i", $id_a_editar);
    mysqli_stmt_execute($stmt_edit);
    $pedido_a_editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_edit));

    // Obtenemos los servicios adicionales de ese pedido
    $stmt_adicionales_edit = mysqli_prepare($conn, "SELECT servicio_id FROM pedido_servicios WHERE pedido_id = ?");
    mysqli_stmt_bind_param($stmt_adicionales_edit, "i", $id_a_editar);
    mysqli_stmt_execute($stmt_adicionales_edit);
    $adicionales_res_edit = mysqli_fetch_all(mysqli_stmt_get_result($stmt_adicionales_edit), MYSQLI_ASSOC);
    $adicionales_del_pedido = array_column($adicionales_res_edit, 'servicio_id'); // Creamos un array simple con los IDs
}


// --- OBTENER DATOS GENERALES PARA FORMULARIOS Y LISTAS ---
// Obtener todos los pedidos para las listas
$query_pedidos = "SELECT p.id, p.nombre_evento, p.fecha_evento, p.precio_final, p.entregado, c.nombre AS cliente_nombre, t.nombre AS tipo_nombre 
                  FROM pedidos p
                  JOIN clientes c ON p.cliente_id = c.id
                  JOIN tipos_invitacion t ON p.tipo_id = t.id
                  ORDER BY p.fecha_creacion DESC";
$resultado = mysqli_query($conn, $query_pedidos);
$pedidos_todos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

$pedidos_nuevos = array_filter($pedidos_todos, function($p) { return $p['entregado'] == 0; });
$pedidos_entregados = array_filter($pedidos_todos, function($p) { return $p['entregado'] == 1; });

// Obtener datos para los <select> del formulario
$clientes_res = mysqli_query($conn, "SELECT id, nombre FROM clientes ORDER BY nombre ASC");
$invitaciones_res = mysqli_query($conn, "SELECT id, nombre, precio_base FROM tipos_invitacion ORDER BY nombre ASC");
$adicionales_res = mysqli_query($conn, "SELECT id, nombre, precio FROM servicios_adicionales ORDER BY nombre ASC");

?>

<div id="pedidos" class="tab-content active">
    <div class="card">
        <h2><?= $pedido_a_editar ? '✅ Editar Pedido' : '✅ Registrar Pedido' ?></h2>
        <form id="pedido-form" class="form-grid-full" method="POST" action="guardar_pedido.php">
            <input type="hidden" name="pedido_id" value="<?= $pedido_a_editar['id'] ?? '' ?>">
            <div class="form-grid">
                <select name="pedido_cliente_id" required>
                    <option value="">-- Seleccionar Cliente --</option>
                    <?php while($cliente = mysqli_fetch_assoc($clientes_res)): ?>
                        <option value="<?= $cliente['id'] ?>" <?= isset($pedido_a_editar) && $pedido_a_editar['cliente_id'] == $cliente['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente['nombre']) ?>
                        </option>
                    <?php endwhile; mysqli_data_seek($clientes_res, 0); ?>
                </select>
                
                <input type="text" name="pedido_nombre_evento" placeholder="Nombre del Evento (ej. Boda)" required value="<?= htmlspecialchars($pedido_a_editar['nombre_evento'] ?? '') ?>">
                
                <select name="pedido_invitacion_id" id="pedido_invitacion_select" required>
                    <option value="" data-precio="0">-- Seleccionar Invitación --</option>
                    <?php while($invitacion = mysqli_fetch_assoc($invitaciones_res)): ?>
                        <option value="<?= $invitacion['id'] ?>" data-precio="<?= $invitacion['precio_base'] ?>" <?= isset($pedido_a_editar) && $pedido_a_editar['tipo_id'] == $invitacion['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($invitacion['nombre']) ?>
                        </option>
                    <?php endwhile; mysqli_data_seek($invitaciones_res, 0); ?>
                </select>

                <input type="date" name="pedido_fecha_evento" required value="<?= htmlspecialchars($pedido_a_editar['fecha_evento'] ?? '') ?>">
                <input type="time" name="pedido_hora_evento" required value="<?= htmlspecialchars($pedido_a_editar['hora_evento'] ?? '') ?>">
                <input type="text" name="pedido_precio" id="pedido_precio_total" placeholder="Precio Total" readonly value="<?= isset($pedido_a_editar) ? number_format($pedido_a_editar['precio_final'], 2) : '' ?>">
            </div>

            <div class="extras-section">
                <h4>Adicionales:</h4>
                <div class="checklist-grid">
                    <?php while($adicional = mysqli_fetch_assoc($adicionales_res)): ?>
                        <div class="checklist-item">
                            <input type="checkbox" class="adicional-checkbox" name="adicionales[]" value="<?= $adicional['id'] ?>" data-precio="<?= $adicional['precio'] ?>" id="adicional-<?= $adicional['id'] ?>" <?= in_array($adicional['id'], $adicionales_del_pedido) ? 'checked' : '' ?>>
                            <label for="adicional-<?= $adicional['id'] ?>"><?= htmlspecialchars($adicional['nombre']) ?> (+ $<?= number_format($adicional['precio'], 2) ?>)</label>
                        </div>
                    <?php endwhile; mysqli_data_seek($adicionales_res, 0); ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="guardar_pedido" class="btn btn-primary">Guardar Pedido</button>
                <?php if ($pedido_a_editar): ?>
                    <a href="pedidos.php" class="btn btn-secondary">Cancelar Edición</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>📋 Lista de Pedidos Pendientes (No Entregados)</h2>
        <table class="data-table">
            <thead>
                <tr><th>Evento</th><th>Cliente</th><th>Invitación</th><th>Precio</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos_nuevos)): ?>
                    <tr><td colspan="5">No hay pedidos nuevos pendientes.</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidos_nuevos as $pedido): ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['nombre_evento']) ?></td>
                        <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($pedido['tipo_nombre']) ?></td>
                        <td>$<?= number_format($pedido['precio_final'], 2) ?></td>
                        <td class="actions">
                            <a href="ver_pedido.php?id=<?= $pedido['id'] ?>" class="btn btn-info">Ver</a>
                            <a href="pedidos.php?action=edit&id=<?= $pedido['id'] ?>" class="btn">Editar</a>
                            <a href="pedidos.php?action=entregar&id=<?= $pedido['id'] ?>" class="btn btn-dark" onclick="return confirm('¿Marcar este pedido como entregado?');">ENTREGADA</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>✓ Invitaciones Entregadas</h2>
        <table class="data-table">
            <thead>
                <tr><th>Evento</th><th>Cliente</th><th>Invitación</th><th>Precio</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos_entregados)): ?>
                    <tr><td colspan="5">No hay pedidos entregados.</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidos_entregados as $pedido): ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['nombre_evento']) ?></td>
                        <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($pedido['tipo_nombre']) ?></td>
                        <td>$<?= number_format($pedido['precio_final'], 2) ?></td>
                        <td class="actions">
                            <a href="ver_pedido.php?id=<?= $pedido['id'] ?>" class="btn btn-info">Ver</a>
                            <span class="status-badge status-entregado">Entregado</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invitacionSelect = document.getElementById('pedido_invitacion_select');
    const precioTotalInput = document.getElementById('pedido_precio_total');
    const adicionalesCheckboxes = document.querySelectorAll('.adicional-checkbox');
    const form = document.getElementById('pedido-form');

    function calcularTotal() {
        let total = 0;
        const selectedOption = invitacionSelect.options[invitacionSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value !== "") {
            const precioBase = parseFloat(selectedOption.getAttribute('data-precio')) || 0;
            total += precioBase;

            adicionalesCheckboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    total += parseFloat(checkbox.getAttribute('data-precio')) || 0;
                }
            });
        }
        
        // Formateamos el precio en el input de solo lectura
        precioTotalInput.value = total > 0 ? '$' + total.toFixed(2) : '';
    }

    invitacionSelect.addEventListener('change', calcularTotal);
    adicionalesCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', calcularTotal);
    });

    // Si hay un pedido para editar, calculamos el total al cargar la página
    if (document.querySelector('input[name="pedido_id"]').value) {
        calcularTotal();
    }
});
</script>

<?php include 'footer.php'; ?>