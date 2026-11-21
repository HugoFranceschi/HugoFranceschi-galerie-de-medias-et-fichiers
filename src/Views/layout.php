<?php
$title = $title ?? 'Galerie Média';
$description = $description ?? 'Galerie de médias et fichiers en ligne.';
$styles = $styles ?? [];
$scripts = $scripts ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= escape($description) ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f172a">
    <title><?= escape($title) ?></title>
    <link rel="icon" href="<?= escape(baseUrl('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= escape(baseUrl('favicon.ico')) ?>" sizes="64x64">
    <link rel="stylesheet" href="<?= escape(baseUrl('style.css')) ?>">
    <?php foreach ($styles as $style): ?>
        <link rel="stylesheet" href="<?= escape(baseUrl($style)) ?>">
    <?php endforeach; ?>
    <?php foreach ($scripts as $script): ?>
        <script src="<?= escape(baseUrl($script)) ?>" defer></script>
    <?php endforeach; ?>
</head>
<body>
    <a class="skip-link" href="#main-content">Aller au contenu</a>
    <main id="main-content" tabindex="-1">
        <?= $content; ?>
    </main>
</body>
</html>
<?php
unset($_SESSION['error']);
unset($_SESSION['old']);
