<?php
ob_start();
?>

<h1>This is the view for a GET method on /!</h1>

<?php

$content = ob_get_clean();
$title = "Methode GET";
$styles = [];
require VIEWS . 'layout.php';
