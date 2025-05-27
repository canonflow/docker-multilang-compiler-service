<?php

namespace DockerMultiLangCompiler\Controllers;

use DockerMultiLangCompiler\Compiler\Language;
use DockerMultiLangCompiler\Dto\CompileDto;
use DockerMultiLangCompiler\Helper\Request;
use DockerMultiLangCompiler\Helper\Response;
use DockerMultiLangCompiler\Helper\TempPath;
use DockerMultiLangCompiler\Helper\Validator;
use DockerMultiLangCompiler\Helper\Verdict;
use DockerMultiLangCompiler\Services\JudgeService;

class JudgeController {
    public function index(): Response
    {
        return Response::Json([
            'status' => "OK",
            'code' => 200,
            'data' => [
                "message" => "DOCKER MULTILANG COMPILER SERVICE - A PHP based service for executing and compiling code in multiple languages using Docker container."
            ]
        ]);
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

        // SETUP ARGUMENT
        $localPath = "";
        $dockerFolder = JudgeService::mkdir();

        // INIT PATH
        $tempFolder = bin2hex(random_bytes(8));
        $codeFilePath = TempPath::get($tempFolder) . "/code";
        $inputFilePath = TempPath::get($tempFolder) . "/input";
        $outputFilePath = TempPath::get($tempFolder) . "/output";

        // INIT NAME
        $codeName = "code" . Language::getExtension($language);
        $inputName = "input.in";

        // MAKE DIRS
        if (!is_dir($codeFilePath)) mkdir($codeFilePath, 0755, true);
        if (!is_dir($inputFilePath)) mkdir($inputFilePath, 0755, true);
        if (!is_dir($outputFilePath)) mkdir($outputFilePath, 0755, true);

        // SAVE FILES
//        $codeFile = fopen($codeFilePath . "/" . $codeName, 'w');
//        fwrite($codeFile, $code);
//        fclose($codeFile);
//
//        $inputFile = fopen($inputFilePath . "/" . $inputName, 'w');
//        fwrite($inputFile, $input);
//        fclose($inputFile);
        $code = stripcslashes($code);
        $input = stripcslashes($input);
        $codeFilePath = $codeFilePath . "/" . $codeName;
        $inputFilePath = $inputFilePath . "/" . $inputName;
        file_put_contents($codeFilePath, $code);
        file_put_contents($inputFilePath, $input);

        // COMPILE CODE
        $compiled = JudgeService::compile($codeFilePath, $dockerFolder, $language);

        // IF COMPILE ERROR
        if ($compiled->status == Verdict::COMPILE_ERROR)
        {
            shell_exec("rm -rf " . TempPath::get($tempFolder));
            return Response::Json([
                "status" => "OK",
                "code" => 200,
                "data" =>[
                    "stdout" => "",
                    "stderr" => $compiled->message,
                    "verdict" => ucwords($compiled->status),
                    "runtime" => 0.000
                ]
            ], 200);
        }

        // JUDGE

        $judged = JudgeService::judge($inputFilePath, $outputFilePath, $dockerFolder, $language, intval($time_limit), intval($memory_limit));
        $folder = TempPath::get($tempFolder);
        shell_exec("rm -rf " . escapeshellarg($folder));

        return Response::Json([
            "status" => "OK",
            "code" => 200,
            "data" =>[
                "stdout" => $judged->stdout,
                "stderr" => $judged->stderr,
                "verdict" => ucwords($judged->status),
                "runtime" => $judged->runtime,
            ]
        ], 200);
    }
}
