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

    public function newImage($id)
    {
        $userId = toPositiveInt($id);

        if ($userId === null) {
            header('Location: ' . baseUrl('login'));
            exit;
        }

        if (
            isset($_SERVER['CONTENT_LENGTH'])
            && (int) $_SERVER['CONTENT_LENGTH'] > (int) convertToBytes(ini_get('post_max_size'))
        ) {
            $this->redirectWithError('Le fichier est trop gros');
        }

        if (!isset($_FILES['src']) || !is_array($_FILES['src'])) {
            $this->redirectWithError('Aucun fichier recu');
        }

        $file = $_FILES['src'];

        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            $this->redirectWithError('Le televersement a echoue');
        }

        $maxSize = (int) convertToBytes(ini_get('upload_max_filesize'));
        if ($maxSize > 0 && (int) $file['size'] > $maxSize) {
            $this->redirectWithError('Le fichier est trop gros');
        }

        $originalName = (string) $file['name'];
        $srcName = pathinfo($originalName, PATHINFO_FILENAME);
        $srcName = trim((string) preg_replace('/[^\pL\pN _.-]/u', '', $srcName));
        $srcName = mb_substr($srcName !== '' ? $srcName : 'fichier', 0, 255);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // On n'accepte que les extensions connues...
        if (!isset(self::ALLOWED_TYPES[$ext])) {
            $this->redirectWithError('Type de fichier non autorise');
        }

        // ...et on vérifie que le contenu correspond réellement à l'extension.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if ($mimeType === false || !in_array($mimeType, self::ALLOWED_TYPES[$ext], true)) {
            $this->redirectWithError('Type de fichier non autorise');
        }

        if (realpath(PUBLIC_DIR) === false) {
            $this->redirectWithError('Erreur interne');
        }

        if (!is_dir(UPLOADS_DIR) && !mkdir(UPLOADS_DIR, 0755, true) && !is_dir(UPLOADS_DIR)) {
            $this->redirectWithError('Erreur interne');
        }

        // Nom généré côté serveur : impossible d'écraser un fichier existant
        // ou de faire exécuter un script via le nom d'origine.
        $fileName = bin2hex(random_bytes(10)) . '.' . $ext;
        $outputFile = '/assets/Img/' . $fileName; // chemin URL stocké en base
        $destination = UPLOADS_DIR . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->redirectWithError('Le televersement a echoue');
        }

        try {
            $req = $this->bdd->prepare(
                "INSERT INTO images (src, id_user, srcName) VALUES (:src, :id_user, :src_name)"
            );
            $req->bindValue(':src', $outputFile, \PDO::PARAM_STR);
            $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
            $req->bindValue(':src_name', $srcName, \PDO::PARAM_STR);
            $req->execute();
        } catch (\PDOException $e) {
            // Pas de fichier orphelin si l'insertion échoue.
            @unlink($destination);
            error_log('Insertion image impossible : ' . $e->getMessage());
            $this->redirectWithError('Erreur interne');
        }

        header("Location: " . baseUrl('/'));
        exit;
    }

    public function find($id)
    {
        $userId = toPositiveInt($id);

        if ($userId === null) {
            return [];
        }

        $stmt = $this->bdd->prepare("SELECT * FROM images WHERE id_user = :id_user");
        $stmt->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_CLASS, "Template\Models\Image");

        return $stmt->fetchAll();
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
