<?php

namespace Template\Models;

abstract class Manager
{
    protected $bdd;

    public function __construct()
    {
        $dsn = 'mysql:host=' . HOST . ';dbname=' . DATABASE . ';charset=utf8mb4';

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            // Requêtes réellement préparées côté MySQL (pas d'émulation client).
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        try {
            $this->bdd = new \PDO($dsn, USER, PASSWORD, $options);
        } catch (\PDOException $e) {
            // On journalise le détail mais on n'affiche jamais l'erreur SQL au visiteur.
            error_log('Connexion a la base impossible : ' . $e->getMessage());
            http_response_code(500);
            exit('Une erreur interne est survenue.');
        }
    }
}
