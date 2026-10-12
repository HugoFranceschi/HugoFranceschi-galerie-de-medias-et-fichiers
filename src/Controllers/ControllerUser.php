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
        $id = $this->manager->register($_POST["user"], $_POST["email"], $_POST["password"]);
        setcookie("id", $id, time() + (60 * 60 * 24 * 365), "/");
        header("Location: /");
    }

    public function login()
    {
        // On vérifier que la requête est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // On récupère les données du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // On vérifie que les champs ne sont pas vides
        if (empty($email) || empty($password)) {
            header("Location: /login");
            exit();
        }

        // On appelle la méthode du manager pour verifier les identifiants
        $user = $this->manager->login($email, $password);

        // On regarde si l'utilisateur existe
        if ($user) {
            $_SESSION['name'] = $user['name'];
            setcookie("id", $user["id"], time() + (60 * 60 * 24 * 365), "/");
            header('Location: /');
            exit;
        } else {
            header('Location: /login');
            exit;
        }
    }
}
