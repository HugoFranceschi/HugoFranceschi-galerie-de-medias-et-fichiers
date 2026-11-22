<?php

namespace Template\Models;

use Template\Models\Manager;

class ManagerCategory extends Manager
{
    public const NAME_MAX_LENGTH = 50;

    /**
     * Toutes les catégories d'un utilisateur, triées par nom.
     *
     * @return Category[]
     */
    public function findAll(int $userId): array
    {
        $req = $this->bdd->prepare(
            "SELECT id, id_user, name
             FROM categories
             WHERE id_user = :id_user
             ORDER BY name ASC"
        );
        $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $req->execute();
        $req->setFetchMode(\PDO::FETCH_CLASS, 'Template\\Models\\Category');

        return $req->fetchAll();
    }

    public function belongsToUser(int $userId, int $categoryId): bool
    {
        $req = $this->bdd->prepare(
            "SELECT id FROM categories WHERE id = :id AND id_user = :id_user LIMIT 1"
        );
        $req->bindValue(':id', $categoryId, \PDO::PARAM_INT);
        $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $req->execute();

        return (bool) $req->fetchColumn();
    }

    /**
     * Crée une catégorie. Retourne son identifiant, ou null en cas d'échec.
     */
    public function create(int $userId, string $name): ?int
    {
        $cleanName = $this->normalizeName($name);

        if ($cleanName === '') {
            return null;
        }

        $existing = $this->findByName($userId, $cleanName);

        if ($existing !== null) {
            return $existing;
        }

        try {
            $req = $this->bdd->prepare(
                "INSERT INTO categories (id_user, name) VALUES (:id_user, :name)"
            );
            $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
            $req->bindValue(':name', $cleanName, \PDO::PARAM_STR);
            $req->execute();

            return (int) $this->bdd->lastInsertId();
        } catch (\PDOException $e) {
            // Deux créations simultanées du même nom : on réutilise celle qui existe.
            $existing = $this->findByName($userId, $cleanName);

            if ($existing !== null) {
                return $existing;
            }

            error_log('Création de catégorie impossible : ' . $e->getMessage());
            return null;
        }
    }

    public function rename(int $userId, int $categoryId, string $name): ?string
    {
        if (!$this->belongsToUser($userId, $categoryId)) {
            return 'Catégorie introuvable.';
        }

        $cleanName = $this->normalizeName($name);

        if ($cleanName === '') {
            return 'Le nom de la catégorie est obligatoire.';
        }

        $existing = $this->findByName($userId, $cleanName);

        if ($existing !== null && $existing !== $categoryId) {
            return 'Une catégorie porte déjà ce nom.';
        }

        try {
            $req = $this->bdd->prepare(
                "UPDATE categories SET name = :name WHERE id = :id AND id_user = :id_user"
            );
            $req->bindValue(':name', $cleanName, \PDO::PARAM_STR);
            $req->bindValue(':id', $categoryId, \PDO::PARAM_INT);
            $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
            $req->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                return 'Une catégorie porte déjà ce nom.';
            }

            error_log('Renommage de catégorie impossible : ' . $e->getMessage());
            return 'Impossible de renommer cette catégorie.';
        }

        return null;
    }

    /**
     * Supprime la catégorie ; ses médias redeviennent « sans catégorie »
     * grâce à la clé étrangère ON DELETE SET NULL.
     */
    public function delete(int $userId, int $categoryId): bool
    {
        $req = $this->bdd->prepare(
            "DELETE FROM categories WHERE id = :id AND id_user = :id_user"
        );
        $req->bindValue(':id', $categoryId, \PDO::PARAM_INT);
        $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $req->execute();

        return $req->rowCount() > 0;
    }

    /**
     * Détermine la catégorie à appliquer à un envoi de fichiers.
     * Une nouvelle catégorie saisie est prioritaire sur la liste déroulante.
     *
     * @return array{id: ?int, error: ?string}
     */
    public function resolveForUpload(int $userId, $categoryId, $newName): array
    {
        if (is_scalar($newName)) {
            $cleanName = $this->normalizeName((string) $newName);

            if ($cleanName !== '') {
                $id = $this->create($userId, $cleanName);

                if ($id === null) {
                    return ['id' => null, 'error' => 'Impossible de créer la catégorie.'];
                }

                return ['id' => $id, 'error' => null];
            }
        }

        $id = toPositiveInt($categoryId);

        if ($id === null) {
            return ['id' => null, 'error' => null];
        }

        if (!$this->belongsToUser($userId, $id)) {
            return ['id' => null, 'error' => 'Catégorie introuvable.'];
        }

        return ['id' => $id, 'error' => null];
    }

    /**
     * Nettoie le nom saisi : espaces superflus supprimés, caractères de
     * contrôle retirés, longueur limitée à 50 caractères.
     */
    public function normalizeName(string $name): string
    {
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
        $name = preg_replace('/\s+/u', ' ', (string) $name);
        $name = trim((string) $name);

        return mb_substr($name, 0, self::NAME_MAX_LENGTH);
    }

    private function findByName(int $userId, string $name): ?int
    {
        $req = $this->bdd->prepare(
            "SELECT id FROM categories WHERE id_user = :id_user AND name = :name LIMIT 1"
        );
        $req->bindValue(':id_user', $userId, \PDO::PARAM_INT);
        $req->bindValue(':name', $name, \PDO::PARAM_STR);
        $req->execute();
        $id = $req->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}
