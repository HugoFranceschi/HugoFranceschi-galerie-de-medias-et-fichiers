<?php

namespace Template\Controllers;

use Template\Controllers\Controller;
use Template\Models\ManagerCategory;
use Template\Models\ManagerImage;
use Template\Models\ManagerUsers;

class ControllerAcceuil extends Controller
{
    private $managerImage;
    private $managerCategory;

    public function __construct()
    {
        parent::__construct(new ManagerUsers());
        $this->managerImage = new ManagerImage();
        $this->managerCategory = new ManagerCategory();
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

        $user = $this->manager->getUser($userId);

        if (!$user) {
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            header("Location: " . baseUrl('login'));
            exit;
        }

        $categories = $this->managerCategory->findAll($userId);

        // Filtre : ?categorie=ID ou ?categorie=none (médias sans catégorie).
        $categoryParam = $_GET['categorie'] ?? null;
        $uncategorized = $categoryParam === 'none';
        $categoryFilter = null;

        if (!$uncategorized && is_scalar($categoryParam)) {
            $candidate = toPositiveInt($categoryParam);

            if ($candidate !== null && $this->managerCategory->belongsToUser($userId, $candidate)) {
                $categoryFilter = $candidate;
            }
        }

        $activeCategory = $uncategorized
            ? 'none'
            : ($categoryFilter !== null ? (string) $categoryFilter : 'all');

        $Users = $this->managerImage->find($userId, $categoryFilter, $uncategorized);

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

        $resolved = $this->managerCategory->resolveForUpload(
            $userId,
            $_POST['category'] ?? null,
            $_POST['new_category'] ?? null
        );

        if ($resolved['error'] !== null) {
            $this->redirectWithError($resolved['error']);
        }

        $this->managerImage->newImages($userId, $resolved['id']);
    }

    public function createCategory()
    {
        $userId = $this->requireUserId();

        if (!verifyCsrfToken()) {
            $this->redirectWithError('Session expirée. Rechargez la page et réessayez.');
        }

        $name = $this->managerCategory->normalizeName((string) ($_POST['name'] ?? ''));

        if ($name === '') {
            $this->redirectWithError('Le nom de la catégorie est obligatoire.');
        }

        if ($this->managerCategory->create($userId, $name) === null) {
            $this->redirectWithError('Impossible de créer la catégorie.');
        }

        $this->redirectWithSuccess('Catégorie enregistrée.');
    }

    public function renameCategory()
    {
        $userId = $this->requireUserId();

        if (!verifyCsrfToken()) {
            $this->redirectWithError('Session expirée. Rechargez la page et réessayez.');
        }

        $categoryId = toPositiveInt($_POST['id'] ?? null);

        if ($categoryId === null) {
            $this->redirectWithError('Catégorie introuvable.');
        }

        $error = $this->managerCategory->rename(
            $userId,
            $categoryId,
            (string) ($_POST['name'] ?? '')
        );

        if ($error !== null) {
            $this->redirectWithError($error);
        }

        $this->redirectWithSuccess('Catégorie renommée.');
    }

    public function deleteCategory()
    {
        $userId = $this->requireUserId();

        if (!verifyCsrfToken()) {
            $this->redirectWithError('Session expirée. Rechargez la page et réessayez.');
        }

        $categoryId = toPositiveInt($_POST['id'] ?? null);

        if ($categoryId === null || !$this->managerCategory->delete($userId, $categoryId)) {
            $this->redirectWithError('Catégorie introuvable.');
        }

        $this->redirectWithSuccess('Catégorie supprimée. Les médias sont conservés sans catégorie.');
    }

    public function renameImage()
    {
        $userId = $this->requireUserId();

        if (!verifyCsrfToken()) {
            $this->redirectWithError('Session expirée. Rechargez la page et réessayez.');
        }

        $imageId = toPositiveInt($_POST['id'] ?? null);

        if ($imageId === null || !$this->managerImage->belongsToUser($imageId, $userId)) {
            $this->redirectWithError('Média introuvable.');
        }

        $error = $this->managerImage->rename(
            $imageId,
            $userId,
            (string) ($_POST['name'] ?? '')
        );

        if ($error !== null) {
            $this->redirectWithError($error);
        }

        $this->redirectWithSuccess('Média renommé.');
    }

    public function setImageCategory()
    {
        $userId = $this->requireUserId();

        if (!verifyCsrfToken()) {
            $this->redirectWithError('Session expirée. Rechargez la page et réessayez.');
        }

        $imageId = toPositiveInt($_POST['id'] ?? null);
        $categoryId = toPositiveInt($_POST['category'] ?? null);

        if ($imageId === null || !$this->managerImage->belongsToUser($imageId, $userId)) {
            $this->redirectWithError('Média introuvable.');
        }

        if (
            $categoryId !== null
            && !$this->managerCategory->belongsToUser($userId, $categoryId)
        ) {
            $this->redirectWithError('Catégorie introuvable.');
        }

        $this->managerImage->setCategory($imageId, $userId, $categoryId);
        $this->redirectWithSuccess('Catégorie du média mise à jour.');
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

    private function requireUserId(): int
    {
        $userId = $this->currentUserId();

        if ($userId === null) {
            header("Location: " . baseUrl('login'));
            exit;
        }

        return $userId;
    }

    private function redirectWithError(string $message): void
    {
        header('Location: ' . baseUrl('/') . '?erreur=' . urlencode($message));
        exit;
    }

    private function redirectWithSuccess(string $message): void
    {
        header('Location: ' . baseUrl('/') . '?succes=' . urlencode($message));
        exit;
    }
}
