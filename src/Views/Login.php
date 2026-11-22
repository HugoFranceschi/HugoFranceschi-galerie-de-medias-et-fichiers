<?php
ob_start();
?>
<div class="bloc login">
    <h1>Connexion</h1>
    <?php if (!empty($_GET['erreur'])): ?>
        <p class="erreur" role="alert"><?= escape($_GET['erreur']) ?></p>
    <?php endif; ?>
    <form action="<?= escape(baseUrl('login')) ?>" method="POST">
        <?= csrfField() ?>
        <div class="text">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" autocomplete="email" required>
        </div>
        <div class="text">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
        </div>
        <div class="lien">
            <button type="submit">Se connecter</button>
            <a href="<?= escape(baseUrl('register')) ?>">Créer un compte</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$title = 'Connexion - Galerie Média';
$description = 'Connexion à la galerie de médias et fichiers.';
$styles = ['user.css'];
require VIEWS . 'layout.php';
