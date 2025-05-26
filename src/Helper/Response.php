<?php

namespace DockerMultiLangCompiler\Helper;

class Response
{
    public static function Json(array $data, int $httpStatusCode = 200)
    {
        http_response_code($httpStatusCode);
        echo json_encode($data);
        die($httpStatusCode);
    }
}