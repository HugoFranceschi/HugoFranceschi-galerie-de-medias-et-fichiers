<?php
ob_start();
?>
<link rel="stylesheet" href="<?= escape(baseUrl('acceuil.css')) ?>">

<header>
    <nav>
        <div class="userName">
            <a href="<?= escape(baseUrl('login')) ?>">Connecter</a>
            <p><?= escape($name) ?></p>
        </div>
        <form action="<?= escape(baseUrl('image')) ?>" class="newImg" method="POST" enctype="multipart/form-data">
            <input type="file" required name="src">
            <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
            <button>Ajoute</button>
            <p id="text" class="erreur"></p>
        </form>
    </nav>
</header>
<div class="grid">
    <?php
    foreach ($Users as $User) {

        $src = $User->getSRC();
        $rawSrcName = $User->getSrcName();
        $cleanSrcName = pathinfo($rawSrcName, PATHINFO_FILENAME);
        $relativeSrc = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim((string) $src, '/\\'));
        $filePath = PUBLIC_DIR . DIRECTORY_SEPARATOR . $relativeSrc;
        $filename = basename($src);

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/zip',
            // 'application/x-zip-compressed',
            // 'multipart/x-zip',
            'audio/mpeg',
            'video/quicktime',
            'video/mp4',
            'video/x-quicktime',
            'application/pdf'
        ];

        // a comprendre
        if (file_exists($filePath) && !is_dir($filePath)) {
            $typeMime = mime_content_type($filePath);

            if (in_array($typeMime, $allowedMimes)) {
    ?>
                <div>
                    <?php
                    // a comprendre
                    if (str_starts_with($typeMime, 'image/')) {
                    ?>
                        <div class="img" style="background-image: url('<?= escape(baseUrl($User->getSRC())) ?>');"></div>
                    <?php
                    } else if (str_starts_with($typeMime, 'application/pdf')) {
                    ?>
                        <div class="img" style="background-image: url('<?= escape(baseUrl('assets/PDF.png')) ?>');"></div>
                    <?php
                    } else if (str_starts_with($typeMime, 'audio/')) {
                    ?>
                        <div class="img">
                            <audio controls controlsList="nodownload">
                                <source src="<?= escape(baseUrl($User->getSRC())) ?>">
                            </audio>
                        </div>
                    <?php
                    } else if (str_starts_with($typeMime, 'video/')) {
                    ?>
                        <div class="img">
                            <video controls controlsList="nodownload">
                                <source src="<?= escape(baseUrl($User->getSRC())) ?>">
                            </video>
                        </div>
                    <?php
                    } else if (str_starts_with($typeMime, 'application/zip')) {
                    ?>
                        <div class="img" style="background-image: url('<?= escape(baseUrl('assets/Zip.png')) ?>');"></div>
                    <?php
                    }
                    ?>

                    <p class="filename"><?= escape($User->getSrcName()) ?></p>
                    <form action="<?= escape(baseUrl('sup')) ?>" class="fichier" method="POST">
                        <a href="<?= escape(baseUrl($User->getSRC())) ?>" download="<?= escape($filename) ?>">Télecharger</a>
                        <input type="hidden" value="<?= (int) $User->getId() ?>" name="id" id="id">
                        <button>Supprimer</button>
                    </form>
                </div>
    <?php
            }
        }
    }
    ?>
</div>

<script>
    let table = []
    getParameters()

    function getParameters() {
        let urlString = window.location.href;
        let paramString = urlString.split('?')[1];
        let queryString = new URLSearchParams(paramString);
        for (let pair of queryString.entries()) {
            table.push(pair[1])
            console.log("Key is: " + pair[0]);
            console.log("Value is: " + pair[1]);
        }
    }

    let text = document.querySelector("#text")
    text.textContent = table[0]
</script>

<?php

$content = ob_get_clean();
$title = "Titre de la page";
$styles = [];
require VIEWS . 'layout.php';
