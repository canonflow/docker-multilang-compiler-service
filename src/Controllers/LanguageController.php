<?php

namespace DockerMultiLangCompiler\Controllers;

use DockerMultiLangCompiler\Helper\Response;
use DockerMultiLangCompiler\Services\JudgeService;

class LanguageController
{
    public static function get(): Response
    {
        $languages = JudgeService::getAllAvailableLanguages();

        return Response::Json([
            "status" => "Ok",
            "code" => 200,
            "data" => $languages
        ]);
    }
}