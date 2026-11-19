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
    $units = ['B', 'K', 'M', 'G', 'T', 'P'];
    $number = substr($from, 0, -1);
    $suffix = strtoupper(substr($from,-1));

    //B or no suffix
    if(is_numeric(substr($suffix, 0, 1))) {
        return preg_replace('/[^\d]/', '', $from);
    }

    $exponent = array_flip($units)[$suffix] ?? null;
    if($exponent === null) {
        return null;
    }

    return $number * (1024 ** $exponent);
}
