<?php
ob_start();
?>

<h1>This is the view for a POST method on /!</h1>

<?php

$content = ob_get_clean();
$title = "Methode POST";
$styles = [];
require VIEWS . 'layout.php';
