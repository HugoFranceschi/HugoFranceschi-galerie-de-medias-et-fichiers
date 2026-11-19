<?php

namespace Template\Controllers;

use Template\Controllers\Controller;
use Template\Models\ManagerUsers;

class ControllerUser extends Controller
{
    public function __construct()
    {
        return parent::__construct(new ManagerUsers());
    }

    public function getLogOut()
    {
        require VIEWS . "LogOut.php";
    }

    public function getLogin()
    {
        require VIEWS . "Login.php";
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $user = trim((string) ($_POST["user"] ?? ''));
        $email = strtolower(trim((string) ($_POST["email"] ?? '')));
        $password = (string) ($_POST["password"] ?? '');

        // Validation stricte avant tout accès à la base.
        if (!preg_match('/^[\pL\pN _.\-]{2,50}$/u', $user)) {
            $this->redirectWithError('/logOut', 'Le nom doit contenir entre 2 et 50 caractères valides.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
            $this->redirectWithError('/logOut', 'Adresse email invalide.');
        }

        // bcrypt ignore silencieusement les octets au-delà de 72.
        if (strlen($password) < 8 || strlen($password) > 72) {
            $this->redirectWithError('/logOut', 'Le mot de passe doit contenir entre 8 et 72 caractères.');
        }

        if ($this->manager->emailExists($email)) {
            $this->redirectWithError('/logOut', 'Un compte existe déjà avec cet email.');
        }

        try {
            $id = $this->manager->register($user, $email, $password);
        } catch (\PDOException $e) {
            error_log('Inscription impossible : ' . $e->getMessage());
            $this->redirectWithError('/logOut', 'Impossible de créer le compte.');
        }

        $this->startUserSession($id, $user);
        header("Location: /");
        exit;
    }

    public function login()
    {
        // On vérifie que la requête est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // On récupère les données du formulaire
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        // On vérifie que les champs ne sont pas vides
        if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('/login', 'Identifiants invalides.');
        }

        // On appelle la méthode du manager pour verifier les identifiants
        $user = $this->manager->login($email, $password);

        // On regarde si l'utilisateur existe
        if ($user) {
            $this->startUserSession((int) $user['id'], (string) $user['name']);
            header('Location: /');
            exit;
        }

        $this->redirectWithError('/login', 'Identifiants invalides.');
    }

    private function startUserSession(int $id, string $name): void
    {
        // Nouvel identifiant de session pour éviter la fixation de session.
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $name;
    }

    private function redirectWithError(string $path, string $message): void
    {
        header('Location: ' . $path . '?erreur=' . urlencode($message));
        exit;
    }
}
