<?php
ob_start();
?>
<link rel="stylesheet" href="<?= escape(baseUrl('user.css')) ?>">

<div class="bloc registeur">
    <h1>Register</h1>
    <?php if (!empty($_GET['erreur'])): ?>
        <p class="erreur"><?= escape($_GET['erreur']) ?></p>
    <?php endif; ?>
    <form action="<?= escape(baseUrl('logOut')) ?>" method="POST">
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
            <a href="<?= escape(baseUrl('login')) ?>">Ce connecter</a>
        </div>
    </form>
</div>

<?php

$content = ob_get_clean();
$title = "Titre de la page";
$styles = [];
require VIEWS . 'layout.php';
