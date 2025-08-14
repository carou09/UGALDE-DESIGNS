<?php
require_once 'header.php';

// --- OBTENER TODOS LOS PEDIDOS Y CALCULAR SALDOS ---
$query = "SELECT 
            p.id, p.nombre_evento, p.precio_final, p.entregado, 
            c.nombre AS cliente_nombre,
            (SELECT SUM(monto) FROM pagos WHERE pedido_id = p.id) AS total_pagado
          FROM pedidos p
          JOIN clientes c ON p.cliente_id = c.id
          WHERE p.entregado = 1 -- Solo nos interesan los pedidos ya entregados
          ORDER BY p.fecha_creacion DESC";

$resultado = mysqli_query($conn, $query);
$pedidos_entregados = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

// Separar los pedidos entregados en "pendientes de pago" y "pagados"
$pedidos_pendientes_pago = [];
$pedidos_pagados = [];

foreach ($pedidos_entregados as $pedido) {
    $total_pagado = $pedido['total_pagado'] ?? 0;
    $saldo = $pedido['precio_final'] - $total_pagado;

    // Si el saldo es mayor a $0.01, sigue pendiente de pago.
    if ($saldo > 0.01) {
        $pedidos_pendientes_pago[] = $pedido;
    } 
    // Si el saldo es cero o insignificante, se considera pagado.
    else {
        $pedidos_pagados[] = $pedido;
    }
}
?>

<div id="pagos" class="tab-content active">
    <div class="card">
        <h2>💸 Invitaciones Pendientes por Pagar</h2>
        <p>Pedidos que ya fueron entregados pero que aún tienen un saldo pendiente.</p>
        <table class="data-table">
            <thead><tr><th>Evento</th><th>Cliente</th><th>Total</th><th>Pagado</th><th>Saldo Pendiente</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php if (empty($pedidos_pendientes_pago)): ?>
                    <tr><td colspan="6">No hay invitaciones pendientes de pago.</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidos_pendientes_pago as $pedido): 
                        $total_pagado = $pedido['total_pagado'] ?? 0;
                        $saldo = $pedido['precio_final'] - $total_pagado;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['nombre_evento']) ?></td>
                        <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                        <td>$<?= number_format($pedido['precio_final'], 2) ?></td>
                        <td>$<?= number_format($total_pagado, 2) ?></td>
                        <td class="saldo-pendiente">$<?= number_format($saldo, 2) ?></td>
                        <td class="actions">
                            <a href="ver_pedido.php?id=<?= $pedido['id'] ?>" class="btn btn-info">Ver</a>
                            <button class="btn btn-danger" onclick="abrirModalPagos(<?= $pedido['id'] ?>)">Agregar Pago</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>✅ Invitaciones Pagadas</h2>
        <p>Estos son los pedidos que ya fueron entregados y están completamente liquidados.</p>
        <table class="data-table">
            <thead><tr><th>Evento</th><th>Cliente</th><th>Total del Pedido</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php if (empty($pedidos_pagados)): ?>
                    <tr><td colspan="5">No hay invitaciones liquidadas.</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidos_pagados as $pedido): ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['nombre_evento']) ?></td>
                        <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                        <td>$<?= number_format($pedido['precio_final'], 2) ?></td>
                        <td><span class="status-badge status-liquidado">Liquidado</span></td>
                        <td class="actions">
                            <a href="ver_pedido.php?id=<?= $pedido['id'] ?>" class="btn btn-info">Ver Pedido</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="pagos-modal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="document.getElementById('pagos-modal').style.display='none'">&times;</span>
        <h2>💳 Registrar un Nuevo Pago</h2>
        <form id="pago-form" method="POST" action="agregar_pago.php">
            <input type="hidden" id="pago-pedido-id" name="pedido_id">
            <div class="form-grid">
                <input type="number" id="pago-monto" name="monto" placeholder="Monto a pagar" step="0.01" required>
                <select id="pago-metodo" name="metodo" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Pago</button>
        </form>
    </div>
</div>

<script>
function abrirModalPagos(pedidoId) {
    document.getElementById('pago-pedido-id').value = pedidoId;
    document.getElementById('pagos-modal').style.display = 'block';
}
</script>

<?php include 'footer.php'; ?>