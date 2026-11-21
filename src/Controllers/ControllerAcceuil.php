<?php

namespace Template\Controllers;

use Template\Controllers\Controller;
use Template\Models\ManagerImage;
use Template\Models\ManagerUsers;

class ControllerAcceuil extends Controller
{
    private $managerImage;

    public function __construct()
    {
        parent::__construct(new ManagerUsers());
        $this->managerImage = new ManagerImage();
    }

    /**
     * L'identité vient uniquement de la session serveur.
     * Le cookie "id" n'est plus accepté : il pouvait être falsifié par le client.
     */
    private function currentUserId(): ?int
    {
        return toPositiveInt($_SESSION['user_id'] ?? null);
    }

    public function getAcceuil()
    {
        $userId = $this->currentUserId();

        if ($userId === null) {
            header("Location: " . baseUrl('login'));
            exit;
        }

        $Users = $this->managerImage->find($userId);
        $user = $this->manager->getUser($userId);

        if (!$user) {
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            header("Location: " . baseUrl('login'));
            exit;
        }

        $name = $user["name"];
        require VIEWS . "Acceuil.php";
    }

    public function postMethod()
    {
        $this->manager->post();
        header("Location: " . baseUrl('/'));
    }

    public function postImg()
    {
        $userId = $this->currentUserId();

        if ($userId === null) {
            header("Location: " . baseUrl('login'));
            exit;
        }

        // Si PHP refuse la requête avant de remplir $_POST, le jeton CSRF
        // est absent : on affiche d'abord un message sur la taille.
        $postMax = (int) convertToBytes((string) ini_get('post_max_size'));

        if (
            $postMax > 0
            && isset($_SERVER['CONTENT_LENGTH'])
            && (int) $_SERVER['CONTENT_LENGTH'] > $postMax
        ) {
            $this->redirectWithError('Les fichiers sont trop gros (limite du serveur).');
        }

        if (!verifyCsrfToken()) {
            $this->redirectWithError('Session expirée. Rechargez la page et réessayez.');
        }

        $this->managerImage->newImages($userId);
    }

    public function sup()
    {
        $userId = $this->currentUserId();
        $imageId = toPositiveInt($_POST["id"] ?? null);

        if ($userId === null) {
            header("Location: " . baseUrl('login'));
            exit;
        }

        if (!verifyCsrfToken()) {
            $this->redirectWithError('Session expirée. Rechargez la page et réessayez.');
        }

        // Un identifiant invalide ne doit jamais atteindre la base.
        if ($imageId === null) {
            header("Location: " . baseUrl('/'));
            exit;
        }

        $this->managerImage->sup($imageId, $userId);
        header("Location: " . baseUrl('/'));
        exit;
    }

    private function redirectWithError(string $message): void
    {
        header('Location: ' . baseUrl('/') . '?erreur=' . urlencode($message));
        exit;
    }
}
