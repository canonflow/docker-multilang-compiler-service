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

    public function judge(): Response
    {
        // VALIDATE REQUEST
        Validator::validate(['language', 'code', 'input', 'time_limit', 'memory_limit']);

        // GET ALL REQUEST
        $language = Request::get('language');
        $code = Request::get('code');
        $input = Request::get('input');
        $time_limit = Request::get('time_limit');
        $memory_limit = Request::get('memory_limit');


        $name = Request::get('name');
        return Response::Json([
            "status" => "OK",
            "code" => 200,
            "data" => [
                "name" => $name
            ],
        ], 200);
    }
}
