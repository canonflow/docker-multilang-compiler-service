<?php

namespace DockerMultiLangCompiler\Helper;

class Request
{
    public static function get(string $key, $default = null): mixed
    {
        if (isset($_POST[$key])) return $_POST[$key];
        if (isset($_GET[$key])) return $_GET[$key];

        return $default;
    }

    public static function file(string $key): mixed
    {
        if (isset($_FILES[$key])) return $_FILES[$key];
        return null;
    }
}