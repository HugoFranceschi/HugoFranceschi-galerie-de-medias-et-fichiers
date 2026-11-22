<?php
ob_start();
?>
<div class="bloc">
    <h1>Page introuvable</h1>
    <p>La page demandée n'existe pas ou a été déplacée.</p>
    <div class="lien">
        <a href="<?= escape(baseUrl('/')) ?>">Retour à la galerie</a>
    </div>
</div>

<?php

$content = ob_get_clean();
$title = 'Page introuvable - Galerie Média';
$description = 'La page demandée n’existe pas ou a été déplacée.';
$styles = ['user.css'];
require VIEWS . 'layout.php';
