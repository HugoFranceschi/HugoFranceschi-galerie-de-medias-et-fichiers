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

            $ext = explode("/", $_FILES["src"]["type"])[1];
            $output_file = "/assets/Img/" . uniqid() . "." . $ext;

            move_uploaded_file($_FILES["src"]["tmp_name"], $publicDir . $output_file);


            $req = $this->bdd->prepare("INSERT INTO images (src, id_user) VALUES (?, ?)");
            $req->execute([
                $output_file,
                $id
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
        $req = $this->bdd->prepare("DELETE FROM images WHERE id LIKE ?");
        $req->execute([
            $id
        ]);

        return;
    }
}
