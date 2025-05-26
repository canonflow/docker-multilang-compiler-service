<?php

namespace DockerMultiLangCompiler\Controllers;

use DockerMultiLangCompiler\Helper\Request;
use DockerMultiLangCompiler\Helper\Response;
use DockerMultiLangCompiler\Helper\Validator;

class JudgeController {
    public function index()
    {
        return "INI DARI CONTROLLER INDEX";
//        Response::Json([
//            'status' => 'OK'
//        ]);
    }

    public function judge()
    {
        Validator::validate(['name', 'age']);

        $name = Request::get('name');
        Response::Json([
            "status" => "OK",
            "code" => 200,
            "data" => [
                "name" => $name
            ],
        ], 200);
    }
}
