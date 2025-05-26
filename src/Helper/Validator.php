<?php

namespace DockerMultiLangCompiler\Helper;

class Validator {
    public static function validate(array $keys)
    {
        $error = [];
        foreach ($keys as $key)
        {
            $tmp = Request::get($key);
            if (is_null($tmp))
            {
                $error[] = "$key is required";
            }
        }

        if (count($error) > 0)
        {
            return Response::Json([
                "status" => "Bad Request",
                "code" => 400,
                "data" => [
                    "message" => $error
                ]
            ]);
        }
    }
}

