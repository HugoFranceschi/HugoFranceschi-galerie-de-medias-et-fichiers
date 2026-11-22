<?php

namespace Template\Models;

use Template\Models\Manager;

class ManagerImage extends Manager
{
    private const ALLOWED_TYPES = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'gif' => ['image/gif'],
        'pdf' => ['application/pdf'],
        'zip' => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
        'mp3' => ['audio/mpeg', 'audio/mp3'],
        'mp4' => ['video/mp4'],
        'mov' => ['video/quicktime'],
    ];

    private const MAX_FILES_PER_REQUEST = 100;

    public function newImages($id, ?int $categoryId = null)
    {
        $userId = toPositiveInt($id);

        if ($userId === null) {
            header('Location: ' . baseUrl('login'));
            exit;
        }

        $postMax = (int) convertToBytes((string) ini_get('post_max_size'));

        if (
            $postMax > 0
            && isset($_SERVER['CONTENT_LENGTH'])
            && (int) $_SERVER['CONTENT_LENGTH'] > $postMax
        ) {
            $this->redirectWithError('Les fichiers sont trop gros.');
        }

        if (!isset($_FILES['src']) || !is_array($_FILES['src'])) {
            $this->redirectWithError('Aucun fichier reçu.');
        }

        $files = $this->normalizeFiles($_FILES['src']);

        if ($files === []) {
            $this->redirectWithError('Aucun fichier reçu.');
        }

        if (count($files) > self::MAX_FILES_PER_REQUEST) {
            $this->redirectWithError(
                'Trop de fichiers à la fois (' . self::MAX_FILES_PER_REQUEST . ' maximum).'
            );
        }

        $added = 0;
        $firstError = null;

        foreach ($files as $file) {
            $error = $this->storeUploadedFile($file, $userId, $categoryId);

            if ($error === null) {
                $added++;
                continue;
            }

            if ($firstError === null) {
                $firstError = $error;
            }
        }

        if ($added === 0) {
            $this->redirectWithError($firstError ?? 'Aucun fichier ajouté.');
        }

        if ($firstError !== null) {
            $this->redirectWithError($this->addedMessage($added) . ' ' . $firstError);
        }

        header('Location: ' . baseUrl('/') . '?succes=' . urlencode($this->addedMessage($added)));
        exit;
    }

    private function addedMessage(int $count): string
    {
        return $count === 1
            ? '1 fichier ajouté.'
            : $count . ' fichiers ajoutés.';
    }

    /**
     * Transforme la structure $_FILES en une liste simple, compatible
     * avec un champ "src" unique ou "src[]" multiple.
     */
    private function normalizeFiles(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }

        if (!is_array($files['name'])) {
            return [[
                'name' => $files['name'],
                'tmp_name' => $files['tmp_name'] ?? '',
                'error' => $files['error'] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'] ?? 0,
            ]];
        }

        $normalized = [];

        foreach ($files['name'] as $index => $name) {
            $normalized[] = [
                'name' => $name,
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    /**
     * Valide et enregistre un fichier. Retourne null en cas de succès,
     * sinon un message d'erreur destiné à l'utilisateur.
     */
    private function storeUploadedFile(array $file, int $userId, ?int $categoryId = null): ?string
    {
        $originalName = (string) ($file['name'] ?? '');
        $displayName = $originalName !== '' ? basename($originalName) : 'fichier';

        if (
            ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))
        ) {
            return 'Le téléversement de « ' . $displayName . ' » a échoué.';
        }

        $maxSize = (int) convertToBytes(ini_get('upload_max_filesize'));
        if ($maxSize > 0 && (int) ($file['size'] ?? 0) > $maxSize) {
            return 'Le fichier « ' . $displayName . ' » est trop gros.';
        }

        $srcName = pathinfo($originalName, PATHINFO_FILENAME);
        $srcName = trim((string) preg_replace('/[^\pL\pN _.-]/u', '', $srcName));
        $srcName = mb_substr($srcName !== '' ? $srcName : 'fichier', 0, 255);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // On n'accepte que les extensions connues...
        if (!isset(self::ALLOWED_TYPES[$ext])) {
            return 'Type de fichier non autorisé : « ' . $displayName . ' ».';
        }

        // ...et on vérifie que le contenu correspond réellement à l'extension.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file((string) $file['tmp_name']);

        if ($mimeType === false || !in_array($mimeType, self::ALLOWED_TYPES[$ext], true)) {
            return 'Type de fichier non autorisé : « ' . $displayName . ' ».';
        }

        if (realpath(PUBLIC_DIR) === false) {
            return 'Erreur interne.';
        }

        if (!is_dir(UPLOADS_DIR) && !mkdir(UPLOADS_DIR, 0755, true) && !is_dir(UPLOADS_DIR)) {
            return 'Erreur interne.';
        }

        // Nom généré côté serveur : impossible d'écraser un fichier existant
        // ou de faire exécuter un script via le nom d'origine.
        $fileName = bin2hex(random_bytes(10)) . '.' . $ext;
        $outputFile = '/assets/Img/' . $fileName; // chemin URL stocké en base
        $destination = UPLOADS_DIR . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
            return 'Le téléversement de « ' . $displayName . ' » a échoué.';
        }

        try {
            $req = $this->bdd->prepare(
                "INSERT INTO images (src, id_user, id_category, srcName)
                 VALUES (:src, :id_user, :id_category, :src_name)"
            );
            $req->bindValue(':src', $outputFile, \PDO::PARAM_STR);
            $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
            $req->bindValue(
                ':id_category',
                $categoryId,
                $categoryId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT
            );
            $req->bindValue(':src_name', $srcName, \PDO::PARAM_STR);
            $req->execute();
        } catch (\PDOException $e) {
            // Pas de fichier orphelin si l'insertion échoue.
            @unlink($destination);
            error_log('Insertion image impossible : ' . $e->getMessage());
            return 'Impossible d’enregistrer « ' . $displayName . ' ».';
        }

        return null;
    }

    /**
     * @param int|null $categoryFilter Identifiant de catégorie, ou null pour tout.
     * @param bool     $uncategorized  Ne garder que les médias sans catégorie.
     * @return Image[]
     */
    public function find($id, ?int $categoryFilter = null, bool $uncategorized = false)
    {
        $userId = toPositiveInt($id);

        if ($userId === null) {
            return [];
        }

        $sql = "SELECT i.*, c.name AS category_name
                FROM images i
                LEFT JOIN categories c ON c.id = i.id_category
                WHERE i.id_user = :id_user";

        if ($uncategorized) {
            $sql .= " AND i.id_category IS NULL";
        } elseif ($categoryFilter !== null) {
            $sql .= " AND i.id_category = :id_category";
        }

        $sql .= " ORDER BY i.id ASC";

        $stmt = $this->bdd->prepare($sql);
        $stmt->bindValue(':id_user', $userId, \PDO::PARAM_INT);

        if (!$uncategorized && $categoryFilter !== null) {
            $stmt->bindValue(':id_category', $categoryFilter, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_CLASS, "Template\Models\Image");

        return $stmt->fetchAll();
    }

    /**
     * Renomme le média affiché. Seul srcName change : le fichier et son URL
     * restent identiques, donc aucun lien existant n'est cassé.
     */
    public function rename(int $imageId, int $userId, string $name): ?string
    {
        $req = $this->bdd->prepare(
            "SELECT src FROM images WHERE id = :id AND id_user = :id_user LIMIT 1"
        );
        $req->bindValue(':id', $imageId, \PDO::PARAM_INT);
        $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $req->execute();
        $src = $req->fetchColumn();

        if ($src === false) {
            return 'Média introuvable.';
        }

        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
        $name = preg_replace('/\s+/u', ' ', (string) $name);
        $name = trim((string) $name);

        // Si l'utilisateur recopie l'extension, on l'enlève : elle est
        // ajoutée automatiquement au téléchargement.
        $extension = strtolower(pathinfo((string) $src, PATHINFO_EXTENSION));

        if ($extension !== '') {
            $name = preg_replace(
                '/\.' . preg_quote($extension, '/') . '$/iu',
                '',
                $name
            );
            $name = trim((string) $name);
        }

        $name = mb_substr($name, 0, 255);

        if ($name === '') {
            return 'Le nom du média est obligatoire.';
        }

        $update = $this->bdd->prepare(
            "UPDATE images SET srcName = :name WHERE id = :id AND id_user = :id_user"
        );
        $update->bindValue(':name', $name, \PDO::PARAM_STR);
        $update->bindValue(':id', $imageId, \PDO::PARAM_INT);
        $update->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $update->execute();

        return null;
    }

    public function belongsToUser(int $imageId, int $userId): bool
    {
        $req = $this->bdd->prepare(
            "SELECT id FROM images WHERE id = :id AND id_user = :id_user LIMIT 1"
        );
        $req->bindValue(':id', $imageId, \PDO::PARAM_INT);
        $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $req->execute();

        return (bool) $req->fetchColumn();
    }

    /**
     * Change la catégorie d'un média. La catégorie doit appartenir au même
     * utilisateur et le média aussi : aucune modification croisée possible.
     */
    public function setCategory(int $imageId, int $userId, ?int $categoryId): bool
    {
        $sql = "UPDATE images SET id_category = :id_category
                WHERE id = :id AND id_user = :id_user";
        $req = $this->bdd->prepare($sql);
        $req->bindValue(
            ':id_category',
            $categoryId,
            $categoryId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT
        );
        $req->bindValue(':id', $imageId, \PDO::PARAM_INT);
        $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $req->execute();

        return $req->rowCount() > 0;
    }

    public function sup($id, $userId)
    {
        $imageId = toPositiveInt($id);
        $ownerId = toPositiveInt($userId);

        if ($imageId === null || $ownerId === null) {
            return;
        }

        // On ne récupère le fichier que s'il appartient bien à l'utilisateur connecté.
        $reqFile = $this->bdd->prepare(
            "SELECT src FROM images WHERE id = :id AND id_user = :id_user LIMIT 1"
        );
        $reqFile->bindValue(':id', $imageId, \PDO::PARAM_INT);
        $reqFile->bindValue(':id_user', $ownerId, \PDO::PARAM_INT);
        $reqFile->execute();
        $image = $reqFile->fetch(\PDO::FETCH_ASSOC);

        if ($image && !empty($image['src'])) {
            $imgDir = realpath(UPLOADS_DIR);

            if ($imgDir !== false) {
                // Le chemin stocké en base est une URL : on le convertit
                // en chemin local valable sur l'OS courant.
                $relativeSrc = str_replace(
                    ['/', '\\'],
                    DIRECTORY_SEPARATOR,
                    ltrim($image['src'], '/\\')
                );
                $filePath = realpath(PUBLIC_DIR . DIRECTORY_SEPARATOR . $relativeSrc);

                // Protection contre toute tentative de traversée de répertoire.
                if (
                    $filePath !== false
                    && str_starts_with($filePath, $imgDir . DIRECTORY_SEPARATOR)
                    && is_file($filePath)
                ) {
                    unlink($filePath);
                }
            }
        }

        // La clause id_user empêche un utilisateur de supprimer l'image d'un autre.
        $req = $this->bdd->prepare(
            "DELETE FROM images WHERE id = :id AND id_user = :id_user"
        );
        $req->bindValue(':id', $imageId, \PDO::PARAM_INT);
        $req->bindValue(':id_user', $ownerId, \PDO::PARAM_INT);
        $req->execute();
    }

    private function redirectWithError(string $message): void
    {
        header('Location: ' . baseUrl('/') . '?erreur=' . urlencode($message));
        exit;
    }
}
