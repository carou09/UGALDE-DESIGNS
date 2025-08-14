<?php 

require_once 'header.php';

$uploadDir = '../uploads/bocetos/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Lógica para subir archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['boceto_file'])) {
    $fileName = time() . '_' . basename($_FILES['boceto_file']['name']);
    $targetFile = $uploadDir . $fileName;
    move_uploaded_file($_FILES['boceto_file']['tmp_name'], $targetFile);
}

// Lógica para eliminar
if (isset($_GET['delete'])) {
    $fileToDelete = $uploadDir . basename($_GET['delete']);
    if (file_exists($fileToDelete)) unlink($fileToDelete);
}
?>
<div class="card">
    <h2>🎨 Gestionar Bocetos del Portafolio</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="boceto_file" required>
        <p>
        <button type="submit" class="btn btn-primary">Subir Boceto</button>
    </form>
</div>
<div class="card">
    <h2>Bocetos Actuales</h2>
    <div class="portfolio-grid">
        <?php
        $bocetos = glob($uploadDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        foreach ($bocetos as $boceto) {
            $fileName = basename($boceto);
            echo "<div>
                    <img src='../uploads/bocetos/{$fileName}' alt='Boceto'>
                    <a href='?delete={$fileName}' class='btn btn-danger' onclick='return confirm(\"¿Eliminar este boceto?\")'>Eliminar</a>
                  </div>";
        }
        ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>