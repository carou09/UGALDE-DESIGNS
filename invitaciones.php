<?php
require_once 'header.php';

// --- PROCESAR FORMULARIO (AÑADIR/EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_invitacion'])) {
    $nombre = $_POST['invitacion_nombre'];
    $precio_base = (float)$_POST['invitacion_precio'];
    $caracteristicas = $_POST['invitacion_caracteristicas'];
    $url_imagen_actual = $_POST['url_imagen_actual'] ?? null;
    $nombre_archivo_nuevo = $url_imagen_actual;

    // Lógica para subir la imagen
    if (isset($_FILES['invitacion_foto']) && $_FILES['invitacion_foto']['error'] == 0) {
        $uploadDir = '../uploads/invitaciones/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $nombre_archivo_nuevo = time() . '_' . basename($_FILES['invitacion_foto']['name']);
        $targetFile = $uploadDir . $nombre_archivo_nuevo;
        move_uploaded_file($_FILES['invitacion_foto']['tmp_name'], $targetFile);
    }
    
    if (!empty($_POST['invitacion_id'])) {
        // UPDATE
        $id = (int)$_POST['invitacion_id'];
        $stmt = mysqli_prepare($conn, "UPDATE tipos_invitacion SET nombre = ?, precio_base = ?, caracteristicas = ?, url_imagen = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sdssi", $nombre, $precio_base, $caracteristicas, $nombre_archivo_nuevo, $id);
        mysqli_stmt_execute($stmt);
    } else {
        // INSERT
        $stmt = mysqli_prepare($conn, "INSERT INTO tipos_invitacion (nombre, precio_base, caracteristicas, url_imagen) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sdss", $nombre, $precio_base, $caracteristicas, $nombre_archivo_nuevo);
        mysqli_stmt_execute($stmt);
    }
    header("Location: invitaciones.php");
    exit;
}

// --- LÓGICA PARA ELIMINAR ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM tipos_invitacion WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: invitaciones.php");
    exit;
}

// --- Cargar datos para edición ---
$invitacion_a_editar = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM tipos_invitacion WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $invitacion_a_editar = mysqli_fetch_assoc($result);
}
?>

<div id="invitaciones" class="tab-content active">
    <div class="card">
        <h2>🎨 Crear/Editar Invitación</h2>
        <form id="invitacion-form" class="form-grid-full" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="invitacion_id" value="<?= $invitacion_a_editar['id'] ?? '' ?>">
            <input type="hidden" name="url_imagen_actual" value="<?= $invitacion_a_editar['url_imagen'] ?? '' ?>">
            
            <h3>Nombre de la invitación:</h3>
            <input type="text" name="invitacion_nombre" placeholder="Standard, Platino, Diamante, etc." required value="<?= htmlspecialchars($invitacion_a_editar['nombre'] ?? '') ?>">
            <h3>Precio de la invitación:</h3>
            <input type="number" name="invitacion_precio" placeholder="Precio" step="0.01" required value="<?= htmlspecialchars($invitacion_a_editar['precio_base'] ?? '') ?>">
            <h3>Descripción de la invitación:</h3>
            <textarea name="invitacion_caracteristicas" placeholder="Características (una por línea)" rows="5" required><?= htmlspecialchars($invitacion_a_editar['caracteristicas'] ?? '') ?></textarea>
            
            <div>
                <h3>Foto de la Invitación:</h3>
                <input type="file" name="invitacion_foto" accept="image/*">
                <?php if (!empty($invitacion_a_editar['url_imagen'])): ?>
                    <img src="../uploads/invitaciones/<?= htmlspecialchars($invitacion_a_editar['url_imagen']) ?>" alt="Vista previa" style="max-width: 100px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            
            <p>
            <button type="submit" name="guardar_invitacion" class="btn btn-primary">Guardar Invitación</button>
            <?php if ($invitacion_a_editar): ?>
                <a href="invitaciones.php" class="btn btn-secondary">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card">
        <h2>Lista de Invitaciones</h2>
        <table class="data-table">
            <thead><tr><th>Foto</th><th>Nombre</th><th>Precio Base</th><th>Características</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php
                $resultado = mysqli_query($conn, "SELECT * FROM tipos_invitacion ORDER BY nombre ASC");
                while ($tipo = mysqli_fetch_assoc($resultado)):
                ?>
                <tr>
                    <td>
                        <?php if (!empty($tipo['url_imagen'])): ?>
                            <img src="../uploads/invitaciones/<?= htmlspecialchars($tipo['url_imagen']) ?>" alt="<?= htmlspecialchars($tipo['nombre']) ?>" style="width: 80px; height: auto; border-radius: 4px;">
                        <?php else: ?>
                            Sin foto
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($tipo['nombre']) ?></td>
                    <td>$<?= number_format($tipo['precio_base'], 2) ?></td>
                    <td><?= nl2br(htmlspecialchars($tipo['caracteristicas'])) ?></td>
                    <td class="actions">
                        <a href="invitaciones.php?action=edit&id=<?= $tipo['id'] ?>" class="btn">Editar</a>
                        <a href="invitaciones.php?action=delete&id=<?= $tipo['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Seguro?');">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>