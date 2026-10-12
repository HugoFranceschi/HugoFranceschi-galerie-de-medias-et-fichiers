<?php

function error($field) {
    return isset($_SESSION["error"][$field]) ? $_SESSION["error"][$field]: "";
}

function old($field) {
    return isset($_SESSION["old"][$field]) ? $_SESSION["old"][$field] : "";
}

function escape($data) {
    return stripslashes(trim(htmlspecialchars($data)));
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
