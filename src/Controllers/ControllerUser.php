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

    public function getRegister()
    {
        require VIEWS . "Register.php";
    }

    /**
     * Ancienne route d'inscription (/logOut) : redirection permanente.
     * Le routeur ne distingue pas la casse, mais les méthodes HTTP sont
     * séparées : GET /logout reste sans effet sur la déconnexion POST.
     */
    public function getLegacyRegister()
    {
        header('Location: ' . baseUrl('register'), true, 301);
        exit;
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

        if (!verifyCsrfToken()) {
            $this->redirectWithError('/register', 'Session expirée. Rechargez la page et réessayez.');
        }

        $user = trim((string) ($_POST["user"] ?? ''));
        $email = strtolower(trim((string) ($_POST["email"] ?? '')));
        $password = (string) ($_POST["password"] ?? '');

        // Validation stricte avant tout accès à la base.
        if (!preg_match('/^[\pL\pN _.\-]{2,50}$/u', $user)) {
            $this->redirectWithError('/register', 'Le nom doit contenir entre 2 et 50 caractères valides.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
            $this->redirectWithError('/register', 'Adresse email invalide.');
        }

        // bcrypt ignore silencieusement les octets au-delà de 72.
        if (strlen($password) < 8 || strlen($password) > 72) {
            $this->redirectWithError('/register', 'Le mot de passe doit contenir entre 8 et 72 caractères.');
        }

        if ($this->manager->emailExists($email)) {
            $this->redirectWithError('/register', 'Un compte existe déjà avec cet email.');
        }

        try {
            $id = $this->manager->register($user, $email, $password);
        } catch (\PDOException $e) {
            error_log('Inscription impossible : ' . $e->getMessage());
            $this->redirectWithError('/register', 'Impossible de créer le compte.');
        }

        $this->startUserSession($id, $user);
        header("Location: " . baseUrl('/'));
        exit;
    }

    public function login()
    {
        // On vérifie que la requête est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!verifyCsrfToken()) {
            $this->redirectWithError('/login', 'Session expirée. Rechargez la page et réessayez.');
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
            header('Location: ' . baseUrl('/'));
            exit;
        }

        $this->redirectWithError('/login', 'Identifiants invalides.');
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . baseUrl('/'));
            exit;
        }

        if (!verifyCsrfToken()) {
            $this->redirectWithError('/login', 'Session expirée. Rechargez la page et réessayez.');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header('Location: ' . baseUrl('login'));
        exit;
    }

    private function startUserSession(int $id, string $name): void
    {
        // Nouvel identifiant de session pour éviter la fixation de session.
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['name'] = $name;

        // Nouveau jeton CSRF après changement d'identité.
        unset($_SESSION['csrf_token']);
    }

    private function redirectWithError(string $path, string $message): void
    {
        header('Location: ' . baseUrl($path) . '?erreur=' . urlencode($message));
        exit;
    }
}
