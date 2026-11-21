<?php
ob_start();
?>
<header>
    <nav>
        <div class="userName">
            <p><?= escape($name) ?></p>
            <form action="<?= escape(baseUrl('logout')) ?>" method="POST" class="logout-form">
                <?= csrfField() ?>
                <button type="submit">Se déconnecter</button>
            </form>
        </div>
        <form action="<?= escape(baseUrl('image')) ?>" class="newImg" method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <label for="media-files" class="sr-only">Choisir un ou plusieurs fichiers (20 maximum)</label>
            <input id="media-files" type="file" name="src[]" accept="image/*,application/pdf,application/zip,audio/mpeg,video/mp4,video/quicktime" multiple required>
            <small class="hint">Plusieurs fichiers possibles (20 max).</small>
            <button type="submit">Ajouter</button>
        </form>
    </nav>
</header>
<?php if (!empty($_GET['erreur'])): ?>
    <p class="erreur page-error" role="alert"><?= escape($_GET['erreur']) ?></p>
<?php endif; ?>
<?php if (!empty($_GET['succes'])): ?>
    <p class="succes page-success" role="status"><?= escape($_GET['succes']) ?></p>
<?php endif; ?>
<div class="grid">
    <?php
    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/zip',
        'application/x-zip-compressed',
        'multipart/x-zip',
        'audio/mpeg',
        'audio/mp3',
        'video/quicktime',
        'video/mp4',
        'video/x-quicktime',
        'application/pdf'
    ];

    foreach ($Users as $User) {
        $src = $User->getSRC();
        $relativeSrc = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim((string) $src, '/\\'));
        $filePath = PUBLIC_DIR . DIRECTORY_SEPARATOR . $relativeSrc;
        $filename = basename((string) $src);

        if (!file_exists($filePath) || is_dir($filePath)) {
            continue;
        }

        $typeMime = mime_content_type($filePath);

        if ($typeMime === false || !in_array($typeMime, $allowedMimes, true)) {
            continue;
        }

        $label = trim((string) $User->getSrcName());

        if ($label === '') {
            $label = $filename;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $downloadName = $extension !== '' ? $label . '.' . $extension : $label;
    ?>
        <div>
            <?php if (str_starts_with($typeMime, 'image/')): ?>
                <img class="img" src="<?= escape(baseUrl($src)) ?>" alt="<?= escape($label) ?>" loading="lazy">
            <?php elseif ($typeMime === 'application/pdf'): ?>
                <img class="img" src="<?= escape(baseUrl('assets/PDF.png')) ?>" alt="Fichier PDF : <?= escape($label) ?>" loading="lazy">
            <?php elseif (str_starts_with($typeMime, 'audio/')): ?>
                <div class="img">
                    <audio controls preload="metadata" aria-label="Lecture audio : <?= escape($label) ?>">
                        <source src="<?= escape(baseUrl($src)) ?>" type="<?= escape($typeMime) ?>">
                    </audio>
                </div>
            <?php elseif (str_starts_with($typeMime, 'video/')): ?>
                <div class="img">
                    <video controls preload="metadata" playsinline aria-label="Lecture vidéo : <?= escape($label) ?>">
                        <source src="<?= escape(baseUrl($src)) ?>" type="<?= escape($typeMime) ?>">
                    </video>
                </div>
            <?php elseif (in_array($typeMime, ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'], true)): ?>
                <img class="img" src="<?= escape(baseUrl('assets/Zip.png')) ?>" alt="Archive ZIP : <?= escape($label) ?>" loading="lazy">
            <?php endif; ?>

            <p class="filename" title="<?= escape($label) ?>"><?= escape($label) ?></p>
            <form action="<?= escape(baseUrl('sup')) ?>" class="fichier" method="POST">
                <?= csrfField() ?>
                <a href="<?= escape(baseUrl($src)) ?>" download="<?= escape($downloadName) ?>" aria-label="Télécharger <?= escape($label) ?>">Télécharger</a>
                <input type="hidden" value="<?= (int) $User->getId() ?>" name="id">
                <button type="submit" aria-label="Supprimer <?= escape($label) ?>">Supprimer</button>
            </form>
        </div>
    <?php
    }
    ?>
</div>

<?php

$content = ob_get_clean();
$title = 'Galerie Média — ' . $name;
$description = 'Galerie de médias et fichiers de ' . $name . '.';
$styles = ['acceuil.css'];
require VIEWS . 'layout.php';
