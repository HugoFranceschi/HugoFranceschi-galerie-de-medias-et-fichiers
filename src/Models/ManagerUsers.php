<?php

namespace Template\Models;

use Template\Models\Manager;

class ManagerUsers extends Manager
{
    public function register($user, $email, $password)
    {
        $user = trim((string) $user);
        $email = strtolower(trim((string) $email));

        $req = $this->bdd->prepare(
            "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)"
        );
        $req->bindValue(':name', $user, \PDO::PARAM_STR);
        $req->bindValue(':email', $email, \PDO::PARAM_STR);
        $req->bindValue(
            ':password',
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            \PDO::PARAM_STR
        );
        $req->execute();

        return (int) $this->bdd->lastInsertId();
    }

    public function login($email, $password)
    {
        $email = strtolower(trim((string) $email));

        $req = $this->bdd->prepare(
            "SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1"
        );
        $req->bindValue(':email', $email, \PDO::PARAM_STR);
        $req->execute();
        $user = $req->fetch(\PDO::FETCH_ASSOC);

        // On vérifie le mot de passe
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        return false; // Mauvais identifiants
    }

    public function getUser($id)
    {
        $id = toPositiveInt($id);

        if ($id === null) {
            return false;
        }

        $req = $this->bdd->prepare(
            "SELECT id, name, email FROM users WHERE id = :id LIMIT 1"
        );
        $req->bindValue(':id', $id, \PDO::PARAM_INT);
        $req->execute();

        return $req->fetch(\PDO::FETCH_ASSOC);
    }

    public function emailExists($email): bool
    {
        $email = strtolower(trim((string) $email));

        $req = $this->bdd->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $req->bindValue(':email', $email, \PDO::PARAM_STR);
        $req->execute();

        return (bool) $req->fetchColumn();
    }
}
