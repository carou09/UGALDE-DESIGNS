<?php
require_once 'header.php';

// --- PROCESAR FORMULARIO (AÑADIR/EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_adicional'])) {
    $nombre = $_POST['adicional_nombre'];
    $precio = (float)$_POST['adicional_precio'];
    
    if (!empty($_POST['adicional_id'])) {
        // UPDATE
        $id = (int)$_POST['adicional_id'];
        $stmt = mysqli_prepare($conn, "UPDATE servicios_adicionales SET nombre = ?, precio = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sdi", $nombre, $precio, $id);
        mysqli_stmt_execute($stmt);
    } else {
        // INSERT
        $stmt = mysqli_prepare($conn, "INSERT INTO servicios_adicionales (nombre, precio) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "sd", $nombre, $precio);
        mysqli_stmt_execute($stmt);
    }
    header("Location: adicionales.php");
    exit;
}

// --- LÓGICA PARA ELIMINAR (DELETE) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM servicios_adicionales WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: adicionales.php");
    exit;
}

// --- Cargar datos para edición ---
$adicional_a_editar = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM servicios_adicionales WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $adicional_a_editar = mysqli_fetch_assoc($result);
}
?>

<div id="adicionales" class="tab-content active">
    <div class="card">
        <h2>⭐ Crear/Editar Adicional</h2>
        <form id="adicional-form" class="form-grid" method="POST">
            <input type="hidden" name="adicional_id" value="<?= $adicional_a_editar['id'] ?? '' ?>">
            <input type="text" name="adicional_nombre" placeholder="Nombre del Adicional" required value="<?= htmlspecialchars($adicional_a_editar['nombre'] ?? '') ?>">
            <input type="number" name="adicional_precio" placeholder="Precio del Adicional" step="0.01" required value="<?= htmlspecialchars($adicional_a_editar['precio'] ?? '') ?>">
            <button type="submit" name="guardar_adicional" class="btn btn-primary">Guardar Adicional</button>
            <?php if ($adicional_a_editar): ?>
                <a href="adicionales.php" class="btn btn-secondary">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card">
        <h2>Lista de Adicionales</h2>
        <table class="data-table">
            <thead><tr><th>Nombre</th><th>Precio</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php
                $resultado = mysqli_query($conn, "SELECT * FROM servicios_adicionales ORDER BY nombre ASC");
                while ($servicio = mysqli_fetch_assoc($resultado)):
                ?>
                <tr>
                    <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                    <td>$<?= number_format($servicio['precio'], 2) ?></td>
                    <td class="actions">
                        <a href="adicionales.php?action=edit&id=<?= $servicio['id'] ?>" class="btn">Editar</a>
                        <a href="adicionales.php?action=delete&id=<?= $servicio['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Seguro?');">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
