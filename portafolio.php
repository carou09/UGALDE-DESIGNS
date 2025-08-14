<div class="portfolio-grid">
    <?php
    $bocetosDir = 'uploads/bocetos/';
    $bocetos = glob($bocetosDir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    if (empty($bocetos)) {
        echo "<p>Aún no hay trabajos en el portafolio.</p>";
    } else {
        foreach ($bocetos as $boceto) {
            echo "<img src='{$boceto}' alt='Boceto de invitación'>";
        }
    }
    ?>
</div>