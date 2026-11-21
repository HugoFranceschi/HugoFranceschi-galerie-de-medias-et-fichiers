<?php

function error($field) {
    return isset($_SESSION["error"][$field]) ? $_SESSION["error"][$field]: "";
}

function old($field) {
    return isset($_SESSION["old"][$field]) ? $_SESSION["old"][$field] : "";
}

function escape($data) {
    if (!is_scalar($data)) {
        return '';
    }

    return htmlspecialchars((string) $data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Construit une URL en tenant compte du préfixe BASE_URL.
 * baseUrl('login') => /login ou /galerie/login
 * baseUrl('/')     => / ou /galerie/
 */
function baseUrl(string $path = ''): string {
    $base = rtrim(BASE_URL, '/');

    // On ne touche pas aux URLs externes.
    if (preg_match('#^(?:https?:)?//#i', $path)) {
        return $path;
    }

    if ($path === '' || $path === '/') {
        return $base === '' ? '/' : $base . '/';
    }

    // Évite de préfixer deux fois un chemin qui contient déjà BASE_URL.
    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        return $path;
    }

    return $base . '/' . ltrim($path, '/');
}

/**
 * Retourne un entier strictement positif, ou null si la valeur est invalide.
 * Utilisé pour tous les identifiants envoyés par le client (cookie, POST, URL).
 */
function toPositiveInt($value): ?int {
    if (!is_scalar($value)) {
        return null;
    }

    $id = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return $id === false ? null : (int) $id;
}

function slugify($str) {
    // replace non letter or digits by -
    $str = preg_replace('~[^\pL\d]+~u', '-', $str);
    // transliterate
    $str = iconv('utf-8', 'us-ascii//TRANSLIT', $str);
    // remove unwanted characters
    $str = preg_replace('~[^-\w]+~', '', $str);
    // trim
    $str = trim($str, '-');
    // remove duplicate -
    $str = preg_replace('~-+~', '-', $str);
    // lowercase
    $str = strtolower($str);
    if (empty($str)) {
        return 'n-a';
    }
    return $str;
}

function convertToBytes(string $from): ?int {
    $from = trim($from);

    if ($from === '') {
        return null;
    }

    // Valeur déjà en octets (ex. "1048576"), ou -1 pour "illimité".
    if (is_numeric($from)) {
        return (int) $from;
    }

    $units = ['B', 'K', 'M', 'G', 'T', 'P'];
    $number = substr($from, 0, -1);
    $suffix = strtoupper(substr($from, -1));
    $exponent = array_flip($units)[$suffix] ?? null;

    if ($exponent === null || !is_numeric($number)) {
        return null;
    }

    return (int) $number * (1024 ** $exponent);
}

/**
 * Retourne le jeton CSRF de la session, en le créant si nécessaire.
 */
function csrfToken(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Champ caché à insérer dans chaque formulaire POST.
 */
function csrfField(): string {
    $token = csrfToken();

    if ($token === '') {
        return '';
    }

    return '<input type="hidden" name="csrf_token" value="' . escape($token) . '">';
}

/**
 * Vérifie le jeton CSRF envoyé par un formulaire POST.
 */
function verifyCsrfToken(): bool {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || $token === '') {
        return false;
    }

    $expected = csrfToken();

    return $expected !== '' && hash_equals($expected, $token);
}
