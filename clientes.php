<?php
require_once 'header.php';

// --- PROCESAR FORMULARIO (AÑADIR/EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cliente'])) {
    $nombre = $_POST['cliente_nombre'];
    $email = $_POST['cliente_email'];
    $telefono = $_POST['cliente_telefono'];
    
    if (!empty($_POST['cliente_id'])) {
        // UPDATE: Editar cliente existente
        $id = $_POST['cliente_id'];
        $stmt = mysqli_prepare($conn, "UPDATE clientes SET nombre = ?, email = ?, telefono = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $telefono, $id);
        mysqli_stmt_execute($stmt);
    } else {
        // INSERT: Añadir nuevo cliente
        $stmt = mysqli_prepare($conn, "INSERT INTO clientes (nombre, email, telefono) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $telefono);
        mysqli_stmt_execute($stmt);
    }
    // Redirige para limpiar el formulario y evitar reenvíos
    header("Location: clientes.php");
    exit;
}

// --- LÓGICA PARA ELIMINAR (DELETE) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM clientes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: clientes.php");
    exit;
}

// --- LÓGICA PARA CARGAR DATOS EN FORMULARIO DE EDICIÓN ---
$cliente_a_editar = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM clientes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $cliente_a_editar = mysqli_fetch_assoc($result);
}
?>

<div id="clientes" class="tab-content active">
    <div class="card">
        <h2><?= $cliente_a_editar ? '✅ Editar Cliente' : '✅ Registrar Cliente' ?></h2>
        <form id="cliente-form" class="form-grid" method="POST" action="clientes.php">
            <input type="hidden" name="cliente_id" value="<?= $cliente_a_editar['id'] ?? '' ?>">
            <input type="text" name="cliente_nombre" placeholder="Nombre del contratante" required value="<?= htmlspecialchars($cliente_a_editar['nombre'] ?? '') ?>">
            <input type="email" name="cliente_email" placeholder="Correo electrónico" required value="<?= htmlspecialchars($cliente_a_editar['email'] ?? '') ?>">
            <input type="tel" name="cliente_telefono" placeholder="Teléfono" required value="<?= htmlspecialchars($cliente_a_editar['telefono'] ?? '') ?>">
            <button type="submit" name="guardar_cliente" class="btn btn-primary">Guardar Cliente</button>
            <?php if ($cliente_a_editar): ?>
                <a href="clientes.php" class="btn btn-secondary">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>
     <div class="card">
        <h2>Lista de Clientes</h2>
        <table class="data-table">
            <thead><tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php
                // SELECT: Leer todos los clientes de la base de datos
                $resultado = mysqli_query($conn, "SELECT * FROM clientes ORDER BY nombre ASC");
                while ($cliente = mysqli_fetch_assoc($resultado)):
                ?>
                <tr>
                    <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                    <td><?= htmlspecialchars($cliente['email']) ?></td>
                    <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                    <td class="actions">
                        <a href="clientes.php?action=edit&id=<?= $cliente['id'] ?>" class="btn">Editar</a>
                        <a href="clientes.php?action=delete&id=<?= $cliente['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar a este cliente?');">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
