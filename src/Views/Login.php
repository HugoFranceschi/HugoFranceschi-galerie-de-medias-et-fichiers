<?php
ob_start();
?>
<link rel="stylesheet" href="user.css">
<div class="bloc login">
    <h1>Login</h1>
    <form action="/login" method="POST">
        <div class="text">
            <p>Email :</p>
            <input type="email" required name="email" >
        </div>
        <div class="text">
            <p>Password :</p>
            <input type="password" required name="password">
        </div>
        <div class="lien">
            <button>Valider</button>
            <a href="/logOut">Creé un compte</a>
        </div>
    </form>
</div>

<?php

$content = ob_get_clean();
$title = "Titre de la page";
$styles = [];
require VIEWS . 'layout.php';
