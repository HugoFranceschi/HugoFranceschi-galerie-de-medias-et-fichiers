<?php

namespace Template\Models;

use Template\Models\Manager;

class ManagerUsers extends Manager
{
    public function register($user, $email, $password)
    {
        $req = $this->bdd->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $req->execute([
            $user,
            $email,
            password_hash($password, PASSWORD_BCRYPT)
        ]);

        return $this->bdd->lastInsertId();
    }

    public function login($email, $password)
    {
        $req = $this->bdd->prepare("SELECT * FROM users WHERE email = ?");
        $req->execute([$email]);
        $user = $req->fetch(\PDO::FETCH_ASSOC);

        // On vérifie le mot de passe
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false; // Mauvais identifiants
    }

    public function getUser($id)
    {
        $req = $this->bdd->prepare("SELECT * FROM users WHERE id = ?");
        $req->execute([$id]);

        return $req->fetch(\PDO::FETCH_ASSOC);
    }
}
