<?php
function change_qwerty($str)
{
    $str_search = array(
        "q", "w", "e", "r", "t", "y", "u", "i", "o", "p", "[", "]",
        "a", "s", "d", "f", "g", "h", "j", "k", "l", ";", "'",
        "z", "x", "c", "v", "b", "n", "m", ",", ".",

        "Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P", "{", "}",
        "A", "S", "D", "F", "G", "H", "J", "K", "L", ";", "'",
        "Z", "X", "C", "V", "B", "N", "M", ",", "."
    );

    $str_replace = array(
        "й", "ц", "у", "к", "е", "н", "г", "ш", "щ", "з", "х", "ъ",
        "ф", "ы", "в", "а", "п", "р", "о", "л", "д", "ж", "э",
        "я", "ч", "с", "м", "и", "т", "ь", "б", "ю",

        "Й", "Ц", "У", "К", "Е", "Н", "Г", "Ш", "Щ", "З", "Х", "Ъ",
        "Ф", "Ы", "В", "А", "П", "Р", "О", "Л", "Д", "Ж", "Э",
        "Я", "Ч", "С", "М", "И", "Т", "Ь", "Б", "Ю"
    );
    return str_replace($str_search, $str_replace, $str);
}

function change_translate($str)
{
    $str_search = array(
        "i", "ts", "u", "k", "e", "n", "g", "sh", "shch", "z", "kh", "ie",
        "f", "y", "v", "a", "p", "r", "o", "l", "d", "zh", "e",
        "ia", "ch", "s", "m", "i", "t", "", "b", "iu", 'e',

        "I", "Ts", "U", "K", "E", "N", "G", "Sh", "Shch", "Z", "Kh", "Ie",
        "F", "Y", "V", "A", "P", "R", "O", "L", "D", "Zh", "E",
        "Ia", "Ch", "S", "M", "I", "T", "", "B", "Iu"
    );

    $str_replace = array(
        "й", "ц", "у", "к", "е", "н", "г", "ш", "щ", "з", "х", "ъ",
        "ф", "ы", "в", "а", "п", "р", "о", "л", "д", "ж", "э",
        "я", "ч", "с", "м", "и", "т", "ь", "б", "ю", 'ё',

        "Й", "Ц", "У", "К", "Е", "Н", "Г", "Ш", "Щ", "З", "Х", "Ъ",
        "Ф", "Ы", "В", "А", "П", "Р", "О", "Л", "Д", "Ж", "Э",
        "Я", "Ч", "С", "М", "И", "Т", "Ь", "Б", "Ю",
    );

    return str_replace($str_replace, $str_search, $str);
}

function search_all($reg, $str)
{
    if (preg_match_all('~(' . $reg . ')~', $str, $match)) {
        return $match[1];
    }
    return null;
}

function str_get($str, $reg)
{
    preg_match('#' . $reg . '#', $str, $match);
    return $match[1] ?? null;
}


function is_phone($value)
{
    ///preg_match('/^7\d{10}$/', $username)
    return is_numeric($value) && strlen((string)$value) > 5;
}
