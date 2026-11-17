<?php

namespace Template\Models;

use Template\Models\Manager;

class ManagerImage extends Manager
{
    public function newImage($id)
    {
        if (
            isset($_SERVER['CONTENT_LENGTH'])
            && (int) $_SERVER['CONTENT_LENGTH'] > convertToBytes(ini_get('post_max_size'))
        ) {
            header("Location: /?errore=Le fichier est tros gros");
            echo ini_get('post_max_size');
            return;
        } else {
            $publicDir = realpath(__DIR__ . '/../../public');
            $ppDir = $publicDir . '/assets/Img';

            if (!is_dir($ppDir)) {
                mkdir($ppDir, 0755, true);
            }

            // a comprendre
            $originalName = $_FILES["src"]["name"];
            $srcName = pathinfo($originalName, PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // a comprendre
            if (empty($ext)) {
                $mimeType = $_FILES["src"]["type"];
                $ext = str_contains($mimeType, 'zip') ? 'zip' : 'bin';
            }

            // $ext = explode("/", $_FILES["src"]["type"])[1];
            $output_file = "/assets/Img/" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES["src"]["tmp_name"], $publicDir . $output_file);

            $req = $this->bdd->prepare("INSERT INTO images (src, id_user, srcName) VALUES (?, ?, ?)");
            $req->execute([
                $output_file,
                $id,
                $srcName
            ]);

            header("Location: /");
            return;
        }
    }

    public function find()
    {
        $stmt = $this->bdd->prepare("SELECT * FROM images WHERE id_user LIKE ?");
        $stmt->execute(array(
            $_COOKIE["id"]
        ));
        $stmt->setFetchMode(\PDO::FETCH_CLASS, "Template\Models\Image");

        return $stmt->fetchAll();
    }

    public function sup($id)
    {
        $reqFile = $this->bdd->prepare("SELECT src FROM images WHERE id = ?");
        $reqFile->execute([$id]);
        $image = $reqFile->fetch();

        if ($image && !empty($image['src'])) {
            $publicDir = realpath(__DIR__ . '/../../public');
            $filePath = $publicDir . $image['src'];

            if (file_exists($filePath) && !is_dir($filePath)) {
                unlink($filePath);
            }
        }

        $req = $this->bdd->prepare("DELETE FROM images WHERE id LIKE ?");
        $req->execute([
            $id
        ]);

        return;
    }
}
