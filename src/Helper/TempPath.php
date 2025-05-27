<?php

namespace DockerMultiLangCompiler\Helper;

class TempPath
{
    public static function get(string $path = ""): string
    {
        $basePath = __DIR__ . "../../../temp";
        return $path ? $basePath . "/" . ltrim($path) : $basePath;
    }
}
