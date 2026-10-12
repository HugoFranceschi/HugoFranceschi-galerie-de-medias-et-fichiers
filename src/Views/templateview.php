<?php
ob_start();
?>

<!-- Contenu de la page -->

<?php

$content = ob_get_clean();
$title = "Titre de la page";
$styles = [];
require VIEWS . 'layout.php';
