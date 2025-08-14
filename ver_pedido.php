<?php
require_once 'header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: Pedido no especificado.");
}
$pedido_id = (int)$_GET['id'];

// --- OBTENCIÓN DE DATOS (SIN CAMBIOS) ---
$query_principal = "SELECT p.*, c.nombre AS cliente_nombre, c.email, c.telefono, t.nombre AS invitacion_nombre, t.caracteristicas,
                    (SELECT SUM(monto) FROM pagos WHERE pedido_id = p.id) AS total_pagado
                    FROM pedidos p
                    JOIN clientes c ON p.cliente_id = c.id
                    JOIN tipos_invitacion t ON p.tipo_id = t.id
                    WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $query_principal);
mysqli_stmt_bind_param($stmt, "i", $pedido_id);
mysqli_stmt_execute($stmt);
$pedido = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$pedido) { die("Error: El pedido no fue encontrado."); }

$total_pagado = $pedido['total_pagado'] ?? 0;
$saldo_pendiente = $pedido['precio_final'] - $total_pagado;

$query_adicionales = "SELECT sa.nombre, sa.precio FROM pedido_servicios ps JOIN servicios_adicionales sa ON ps.servicio_id = sa.id WHERE ps.pedido_id = ?";
$stmt_adicionales = mysqli_prepare($conn, $query_adicionales);
mysqli_stmt_bind_param($stmt_adicionales, "i", $pedido_id);
mysqli_stmt_execute($stmt_adicionales);
$adicionales = mysqli_fetch_all(mysqli_stmt_get_result($stmt_adicionales), MYSQLI_ASSOC);

$query_pagos = "SELECT * FROM pagos WHERE pedido_id = ? ORDER BY fecha_pago DESC";
$stmt_pagos = mysqli_prepare($conn, $query_pagos);
mysqli_stmt_bind_param($stmt_pagos, "i", $pedido_id);
mysqli_stmt_execute($stmt_pagos);
$historial_pagos = mysqli_fetch_all(mysqli_stmt_get_result($stmt_pagos), MYSQLI_ASSOC);
?>

<div class="tab-content active">
    <div class="card">
        <div class="card-header">
            <h2>📄 Resumen del Pedido #<?= htmlspecialchars($pedido['id']) ?></h2>
            <a href="pedidos.php" class="btn btn-secondary">Volver a Pedidos</a>
        </div>
        
        <div class="pedido-detalle-grid">
             <div class="detalle-seccion">
                <h3>Detalles del Cliente y Evento</h3>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['cliente_nombre']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($pedido['email']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['telefono']) ?></p>
                <hr>
                <p><strong>Evento:</strong> <?= htmlspecialchars($pedido['nombre_evento']) ?></p>
                <p><strong>Fecha:</strong> <?= date("d/m/Y", strtotime($pedido['fecha_evento'])) ?></p>
                <p><strong>Hora:</strong> <?= date("g:i A", strtotime($pedido['hora_evento'])) ?></p>
            </div>
            <div class="detalle-seccion">
                <h3>Detalles de la Invitación</h3>
                <p><strong>Tipo:</strong> <?= htmlspecialchars($pedido['invitacion_nombre']) ?></p>
                <p><strong>Características Incluidas:</strong></p>
                <ul>
                    <?php foreach(explode("\n", $pedido['caracteristicas']) as $caracteristica): ?>
                        <?php if(trim($caracteristica) != ''): ?>
                            <li><?= htmlspecialchars(trim($caracteristica)) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <?php if (!empty($adicionales)): ?>
                    <h4 style="margin-top: 20px;">Adicionales Contratados:</h4>
                    <ul>
                        <?php foreach($adicionales as $adicional): ?>
                            <li><?= htmlspecialchars($adicional['nombre']) ?> - $<?= number_format($adicional['precio'], 2) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="total-section" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid var(--primary-color);">
            <h3>Precio Total del Pedido: $<?= number_format($pedido['precio_final'], 2) ?> MXN</h3>
            <?php if ($saldo_pendiente > 0.01): ?>
                <h3 class="saldo-pendiente">Total Pendiente: $<?= number_format($saldo_pendiente, 2) ?> MXN</h3>
            <?php else: ?>
                <h3 class="saldo-liquidado">✓ Pedido Liquidado</h3>
            <?php endif; ?>
        </div>
        
        <div class="form-actions" style="margin-top: 20px; display: flex; gap: 10px;">
            <?php if ($saldo_pendiente > 0.01): ?>
                <button class="btn btn-danger" onclick="abrirModalPagos(<?= $pedido['id'] ?>)">AGREGAR PAGO</button>
            <?php else: ?>
                <button class="btn btn-info" onclick="abrirHistorialPagos()">VER LISTA DE PAGOS</button>
            <?php endif; ?>
            
            <button onclick="generarContratoPDF()" class="btn btn-primary">Imprimir Pedido</button>
        </div>
    </div>
</div>

<div id="pagos-modal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="document.getElementById('pagos-modal').style.display='none'">&times;</span>
        <h2>💳 Registrar un Nuevo Pago</h2>
        <form id="pago-form" method="POST" action="agregar_pago.php">
            <input type="hidden" id="pago-pedido-id" name="pedido_id" value="<?= $pedido_id ?>">
            <input type="hidden" name="source" value="ver_pedido">
            <div class="form-grid">
                <input type="number" name="monto" placeholder="Monto a pagar" step="0.01" required>
                <select name="metodo" required>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Tarjeta">Tarjeta</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Pago</button>
        </form>
    </div>
</div>
<div id="pagos-historial-modal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="document.getElementById('pagos-historial-modal').style.display='none'">&times;</span>
        <h2>Historial de Pagos</h2>
        <table class="data-table">
            <thead><tr><th>Fecha</th><th>Monto</th><th>Método</th></tr></thead>
            <tbody>
                <?php if (empty($historial_pagos)): ?>
                    <tr><td colspan="3">No hay pagos registrados para este pedido.</td></tr>
                <?php else: ?>
                    <?php foreach ($historial_pagos as $pago): ?>
                    <tr>
                        <td><?= date("d/m/Y h:i A", strtotime($pago['fecha_pago'])) ?></td>
                        <td>$<?= number_format($pago['monto'], 2) ?></td>
                        <td><?= htmlspecialchars($pago['metodo']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="pdf-container-hidden" style="position: fixed; visibility: hidden; top: 0; left: 0; z-index: -100;"></div>

<script src="https://unpkg.com/pdf-lib"></script>
<script src="https://unpkg.com/@pdf-lib/fontkit"></script>

<script>
    const pedido = <?= json_encode($pedido); ?>;
    const adicionales = <?= json_encode($adicionales); ?>;

    async function generarContratoPDF() {
        const { PDFDocument, rgb, StandardFonts } = PDFLib;

        try {
            const pdfDoc = await PDFDocument.create();
            const page = pdfDoc.addPage([595.28, 841.89]); // A4
            const { width, height } = page.getSize();

            const helveticaFont = await pdfDoc.embedFont(StandardFonts.Helvetica);
            const helveticaBoldFont = await pdfDoc.embedFont(StandardFonts.HelveticaBold);
            
            // --- COLORES CORREGIDOS ---
            const primaryColor = rgb(0.1, 0.1, 0.1);
            const secondaryColor = rgb(0.4, 0.4, 0.4);
            const black = rgb(0, 0, 0); // Corregido: Usando rgb()
            // Corregido: Usando rgb() y valores de 0 a 1
            const purple = rgb(175 / 255, 0 / 255, 175 / 255); 

            const margin = 50;
            let y = height - margin;

            // --- TÍTULO CORREGIDO (Color y centrado) ---
            page.drawText('CONTRATACIÓN DE SERVICIOS', {
                x: width / 5, // Centrado correctamente
                y: y,
                font: helveticaBoldFont,
                size: 22,
                color: purple, // Color morado aplicado
                align: 'center'
            });
            y -= 45;

            // Detalles del Cliente
            page.drawText('Detalles del Cliente', { x: margin, y: y, font: helveticaBoldFont, size: 14, color: black });
            y -= 25;
            page.drawText(`Cliente: ${pedido.cliente_nombre}`, { x: margin, y: y, font: helveticaFont, size: 11, color: secondaryColor });
            y -= 20;
            page.drawText(`Email: ${pedido.email}`, { x: margin, y: y, font: helveticaFont, size: 11, color: secondaryColor });
            y -= 20;
            page.drawText(`Teléfono: ${pedido.telefono}`, { x: margin, y: y, font: helveticaFont, size: 11, color: secondaryColor });
            y -= 25;
            page.drawLine({ start: { x: margin, y: y }, end: { x: width - margin, y: y }, thickness: 0.5, color: rgb(0.8, 0.8, 0.8) });
            y -= 30;

            // Detalles del Evento
            page.drawText('Detalles del Evento', { x: margin, y: y, font: helveticaBoldFont, size: 14, color: black });
            y -= 25;
            page.drawText(`Tipo de evento a realizar: ${pedido.nombre_evento}`, { x: margin, y: y, font: helveticaFont, size: 11, color: secondaryColor });
            y -= 20;
            const fechaEvento = new Date(pedido.fecha_evento + 'T00:00:00');
            page.drawText(`Fecha del evento: ${fechaEvento.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })}`, { x: margin, y: y, font: helveticaFont, size: 11, color: secondaryColor });
            y -= 20;
            const horaEvento = new Date(`1970-01-01T${pedido.hora_evento}`);
            page.drawText(`Hora del evento: ${horaEvento.toLocaleTimeString('en-US', { hour: '2-digit', minute:'2-digit', hour12: true })}`, { x: margin, y: y, font: helveticaFont, size: 11, color: secondaryColor });
            y -= 30;
            page.drawLine({ start: { x: margin, y: y }, end: { x: width - margin, y: y }, thickness: 0.5, color: rgb(0.8, 0.8, 0.8) });
            y -= 30;
            
            // Detalles de la Invitación
            page.drawText('Detalles de la Invitación', { x: margin, y: y, font: helveticaBoldFont, size: 14, color: black });
            y -= 25;
            page.drawText(`Tipo de invitación: ${pedido.invitacion_nombre}`, { x: margin, y: y, font: helveticaFont, size: 11, color: secondaryColor });
            y -= 30;
            
            page.drawText('Descripción del servicio:', { x: margin, y: y, font: helveticaBoldFont, size: 11, color: black });
            y -= 18;
            if (pedido.caracteristicas) {
                pedido.caracteristicas.split('\n').forEach(line => {
                    if (line.trim() !== '') {
                        page.drawText(`• ${line.trim()}`, { x: margin + 10, y: y, font: helveticaFont, size: 10, color: secondaryColor });
                        y -= 15;
                    }
                });
            }

            // Adicionales
            if (adicionales && adicionales.length > 0) {
                y -= 10;
                page.drawLine({ start: { x: margin, y: y }, end: { x: width - margin, y: y }, thickness: 0.5, color: rgb(0.8, 0.8, 0.8) });
                y -= 30;
                page.drawText('Adicionales Contratados:', { x: margin, y: y, font: helveticaBoldFont, size: 14, color: black });
                y -= 25;
                adicionales.forEach(adicional => {
                    const precioFormateado = `$${parseFloat(adicional.precio).toFixed(2)}`;
                    page.drawText(`• ${adicional.nombre} - ${precioFormateado}`, { x: margin + 10, y: y, font: helveticaFont, size: 10, color: secondaryColor });
                    y -= 15;
                });
            }
            y -= 20;
            page.drawLine({ start: { x: margin, y: y }, end: { x: width - margin, y: y }, thickness: 1, color: black });
            y -= 40;

            // --- TOTALES ---
            const totalPagado = parseFloat(pedido.total_pagado || 0);
            const saldoPendiente = parseFloat(pedido.precio_final) - totalPagado;
            const totalText = `Precio Total: $${parseFloat(pedido.precio_final).toFixed(2)} MXN`;
            const pagadoText = `Total Pagado: $${totalPagado.toFixed(2)} MXN`;
            const pendienteText = `Saldo Pendiente: $${saldoPendiente.toFixed(2)} MXN`;

            page.drawText(totalText, { x: width / 1.4 - margin, y: y, font: helveticaBoldFont, size: 14, color: black, align: 'right' });
            y -= 25;
            page.drawText(pagadoText, { x: width / 1.4 - margin, y: y, font: helveticaFont, size: 12, color: secondaryColor, align: 'right' });
            y -= 25;
            page.drawText(pendienteText, { x: width / 1.4 - margin, y: y, font: helveticaBoldFont, size: 13, color: primaryColor, align: 'right' });


            // --- PIE DE PÁGINA ---
            const footerY = 60;
            page.drawLine({ start: { x: margin, y: footerY + 20 }, end: { x: width - margin, y: footerY + 20 }, thickness: 1, color: black });
            page.drawText('DISEÑOS UGALDE', {
                x: width / 10, y: footerY,
                font: helveticaBoldFont, size: 10, color: black, align: 'center'
            });
            page.drawText('Contacto: 81 2616 8533', {
                x: width / 10, y: footerY - 15,
                font: helveticaFont, size: 9, color: secondaryColor, align: 'center'
            });
            page.drawText('Correo: ugalde.designs@gmail.com', {
                x: width / 10, y: footerY - 28,
                font: helveticaFont, size: 9, color: secondaryColor, align: 'center'
            });

            // --- Guardar y descargar ---
            const pdfBytes = await pdfDoc.save();
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `Contrato_${pedido.id}-${pedido.cliente_nombre.replace(/ /g, '_')}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

        } catch (err) {
            console.error("Error al generar PDF:", err);
            alert("Ocurrió un error al generar el PDF.");
        }
    }

    function abrirModalPagos(pedidoId) {
        document.getElementById('pago-pedido-id').value = pedidoId;
        document.getElementById('pagos-modal').style.display = 'block';
    }
    function abrirHistorialPagos() {
        document.getElementById('pagos-historial-modal').style.display = 'block';
    }
</script>

<?php include 'footer.php'; ?>