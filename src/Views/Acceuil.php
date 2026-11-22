<?php
ob_start();
$postMaxLabel = trim((string) ini_get('post_max_size'));
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
            <div class="newImg-fields">
                <label for="media-files" class="sr-only">Choisir un ou plusieurs fichiers (100 maximum)</label>
                <input id="media-files" type="file" name="src[]" accept="image/*,application/pdf,application/zip,audio/mpeg,video/mp4,video/quicktime" multiple required>

                <label for="media-category" class="sr-only">Catégorie des fichiers envoyés</label>
                <select id="media-category" name="category">
                    <option value="">Sans catégorie</option>
                    <?php foreach ($categories as $Category): ?>
                        <option value="<?= (int) $Category->getId() ?>"><?= escape($Category->getName()) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="new-category" class="sr-only">Ou créer une nouvelle catégorie</label>
                <input id="new-category" type="text" name="new_category" maxlength="50" placeholder="Ou nouvelle catégorie…">

                <button type="submit">Ajouter</button>
            </div>
            <small class="hint">Plusieurs fichiers possibles (100 max<?= $postMaxLabel !== '' ? ', ' . escape($postMaxLabel) . ' au total' : '' ?>). La catégorie choisie s’applique à tout l’envoi.</small>
        </form>
    </nav>
</header>
<?php if (!empty($_GET['erreur'])): ?>
    <p class="erreur page-error" role="alert"><?= escape($_GET['erreur']) ?></p>
<?php endif; ?>
<?php if (!empty($_GET['succes'])): ?>
    <p class="succes page-success" role="status"><?= escape($_GET['succes']) ?></p>
<?php endif; ?>

<section class="categories" aria-labelledby="categories-title">
    <h2 id="categories-title">Catégories</h2>
    <ul class="category-list">
        <li>
            <a class="category-chip" href="<?= escape(baseUrl('/')) ?>" <?= $activeCategory === 'all' ? ' aria-current="page"' : '' ?>>Toutes</a>
        </li>
        <?php foreach ($categories as $Category): ?>
            <?php $categoryId = (string) (int) $Category->getId(); ?>
            <li>
                <a class="category-chip" href="<?= escape(baseUrl('/')) ?>?categorie=<?= $categoryId ?>" <?= $activeCategory === $categoryId ? ' aria-current="page"' : '' ?>><?= escape($Category->getName()) ?></a>
            </li>
        <?php endforeach; ?>
        <li>
            <a class="category-chip" href="<?= escape(baseUrl('/')) ?>?categorie=none" <?= $activeCategory === 'none' ? ' aria-current="page"' : '' ?>>Sans catégorie</a>
        </li>
    </ul>

    <details class="category-manage">
        <summary>Gérer les catégories</summary>

        <form action="<?= escape(baseUrl('categorie')) ?>" method="POST" class="category-create">
            <?= csrfField() ?>
            <label for="category-name">Nouvelle catégorie</label>
            <input id="category-name" type="text" name="name" maxlength="50" placeholder="Vacances, Travail…" required>
            <button type="submit">Créer</button>
        </form>

        <?php if ($categories !== []): ?>
            <ul class="category-manage-list">
                <?php foreach ($categories as $Category): ?>
                    <?php $categoryId = (int) $Category->getId(); ?>
                    <li>
                        <form action="<?= escape(baseUrl('categorie/renommer')) ?>" method="POST" class="category-rename">
                            <?= csrfField() ?>
                            <label class="sr-only" for="rename-category-<?= $categoryId ?>">Renommer <?= escape($Category->getName()) ?></label>
                            <input id="rename-category-<?= $categoryId ?>" type="text" name="name" value="<?= escape($Category->getName()) ?>" maxlength="50" required>
                            <input type="hidden" name="id" value="<?= $categoryId ?>">
                            <button type="submit">Renommer</button>
                        </form>
                        <form action="<?= escape(baseUrl('categorie/supprimer')) ?>" method="POST" class="category-delete" data-confirm="Supprimer la catégorie « <?= escape($Category->getName()) ?> » ? Les médias seront conservés sans catégorie.">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $categoryId ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="category-empty">Aucune catégorie pour le moment.</p>
        <?php endif; ?>
    </details>
</section>

<?php if ($Users === []): ?>
    <p class="empty-gallery">Aucun média dans cette sélection. Ajoute un fichier ou choisis une autre catégorie.</p>
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
        $imageId = (int) $User->getId();
        $imageCategoryId = $User->getId_category() !== null ? (int) $User->getId_category() : null;
        $imageCategoryName = trim((string) $User->getCategoryName());
    ?>
        <div class="card">
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
            <p class="category-label"><?= $imageCategoryName !== '' ? escape($imageCategoryName) : 'Sans catégorie' ?></p>

            <details class="rename-media">
                <summary>Renommer</summary>
                <form action="<?= escape(baseUrl('image/renommer')) ?>" method="POST" class="rename-form">
                    <?= csrfField() ?>
                    <label class="sr-only" for="rename-image-<?= $imageId ?>">Nouveau nom de <?= escape($label) ?></label>
                    <input id="rename-image-<?= $imageId ?>" type="text" name="name" value="<?= escape($label) ?>" maxlength="255" required>
                    <input type="hidden" name="id" value="<?= $imageId ?>">
                    <button type="submit">Enregistrer</button>
                </form>
            </details>

            <form action="<?= escape(baseUrl('categorie/image')) ?>" method="POST" class="category-form">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $imageId ?>">
                <label class="sr-only" for="image-category-<?= $imageId ?>">Catégorie de <?= escape($label) ?></label>
                <select id="image-category-<?= $imageId ?>" name="category">
                    <option value="">Sans catégorie</option>
                    <?php foreach ($categories as $Category): ?>
                        <option value="<?= (int) $Category->getId() ?>" <?= $imageCategoryId === (int) $Category->getId() ? ' selected' : '' ?>><?= escape($Category->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Classer</button>
            </form>

            <form action="<?= escape(baseUrl('sup')) ?>" class="fichier" method="POST" data-confirm="Supprimer définitivement « <?= escape($label) ?> » ?">
                <?= csrfField() ?>
                <a href="<?= escape(baseUrl($src)) ?>" download="<?= escape($downloadName) ?>" aria-label="Télécharger <?= escape($label) ?>">Télécharger</a>
                <input type="hidden" value="<?= $imageId ?>" name="id">
                <button type="submit" aria-label="Supprimer <?= escape($label) ?>">Supprimer</button>
            </form>
        </div>
    <?php
    }
    ?>
</div>

<?php

$content = ob_get_clean();
$title = 'Galerie Média - ' . $name;
$description = 'Galerie de médias et fichiers de ' . $name . '.';
$styles = ['acceuil.css'];
$scripts = ['acceuil.js'];
require VIEWS . 'layout.php';
