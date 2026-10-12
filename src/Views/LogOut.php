<?php
ob_start();
?>
<link rel="stylesheet" href="user.css">

<div class="bloc registeur">
    <h1>Register</h1>
    <form action="/logOut" method="POST">
        <div class="text">
            <p>User :</p>
            <input type="text" required name="user">
        </div>
        <div class="text">
            <p>Email :</p>
            <input type="email" required name="email">
        </div>
        <div class="text">
            <p>Password :</p>
            <input type="password" required name="password">
        </div>
        <div class="lien">
            <button type="submit">Enregistre</button>
            <a href="/login">Ce connecter</a>
        </div>
    </form>
</div>

<?php

$content = ob_get_clean();
$title = "Titre de la page";
$styles = [];
require VIEWS . 'layout.php';
