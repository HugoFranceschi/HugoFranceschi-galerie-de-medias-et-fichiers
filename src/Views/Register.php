<?php
ob_start();
?>
<div class="bloc register">
    <h1>Inscription</h1>
    <?php if (!empty($_GET['erreur'])): ?>
        <p class="erreur" role="alert"><?= escape($_GET['erreur']) ?></p>
    <?php endif; ?>
    <form action="<?= escape(baseUrl('register')) ?>" method="POST">
        <?= csrfField() ?>
        <div class="text">
            <label for="user">Nom d'utilisateur</label>
            <input id="user" type="text" name="user" autocomplete="username" minlength="2" maxlength="50" required>
        </div>
        <div class="text">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" autocomplete="email" maxlength="254" required>
        </div>
        <div class="text">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" aria-describedby="password-help" required>
            <small class="hint" id="password-help">8 caractères minimum.</small>
        </div>
        <div class="lien">
            <button type="submit">S'inscrire</button>
            <a href="<?= escape(baseUrl('login')) ?>">Se connecter</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$title = 'Inscription - Galerie Média';
$description = 'Création d’un compte pour la galerie de médias et fichiers.';
$styles = ['user.css'];
require VIEWS . 'layout.php';
