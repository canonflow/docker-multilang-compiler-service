<?php

namespace DockerMultiLangCompiler\Services;

use DockerMultiLangCompiler\Compiler\Language;
use DockerMultiLangCompiler\Dto\CompileDto;
use DockerMultiLangCompiler\Dto\JudgeDto;
use DockerMultiLangCompiler\Helper\Response;
use DockerMultiLangCompiler\Helper\Verdict;

class JudgeService {

    private const USER = "executor";
    private const CONTAINER = "docker_compiler";
    private const TIME_LIMIT_STRING = "CPU time limit exceeded";
    private const MEMORY_LIMIT_STRING = "Memory size limit exceeded";

    private static function filterCompilerError($errorMessage) {
        // Regular expressions to match common file path patterns
        $patterns = [
            // Windows style paths - escaped properly
            '~[A-Za-z]:\\\\(?:[^\\\\/:*?"<>|\r\n]+\\\\)*[^\\\\/:*?"<>|\r\n]*~',
            // Unix style paths - escaped properly
            '~(?:/[^/\s]+)+~',
            // Line with just file path - escaped properly
            '~^.*[/\\\\].*$~m'
        ];

        // Remove lines containing only file paths
        $filteredError = $errorMessage;
        foreach ($patterns as $pattern) {
            $filteredError = preg_replace($pattern, '', $filteredError);
        }

        // Remove empty lines that might be left after filtering
        $filteredError = preg_replace('~^\s*[\r\n]~m', '', $filteredError);

        // Trim extra whitespace
        return trim($filteredError);
    }

    public static function parseRuntime($stream)
    {
        $str = strstr(
            $stream,
            "real"
        );
        $str = str_replace(
            ",",
            ".",
            $str
        );
        $im = strpos($str, "m");
        $is = strpos($str, "s");
        $m = intval(substr($str, 5, $im - 5));
        $s = intval(substr($str, $im + 1, $is - $im - 1));
        $runtime = number_format($m * 60 + $s, 3);
        return $runtime;
    }

    public static function mkdir()
    {
        $random = bin2hex(random_bytes(8));
        $folder = "/home/" . self::USER . "/workspace/" . $random;
//        $folder = "/home/" . self::USER . "/" . $random;

//        $command = "docker exec " . self::CONTAINER . " bash -c 'mkdir -p $folder && rm -rf $folder/*'";
        $command = 'docker exec ' . self::CONTAINER . ' bash -c ' . escapeshellarg("mkdir -p $folder && rm -rf $folder/*");
        shell_exec($command);
        return $folder;
    }

    public static function getAllAvailableLanguages(): array
    {
        return Language::LANGUAGES;
    }

    public static function isLanguageAvailable(string $language): bool
    {
        return in_array(strtolower($language), Language::LANGUAGES);
    }
    public static function compile($codeFilePath, $dockerFolder, $language): CompileDto
    {
        if (!JudgeService::isLanguageAvailable($language))
        {
            return Response::Json([
                "status" => "Bad Request",
                "code" => 400,
                "data" => [
                    "message" => "Language is not supported"
                ]
            ]);
        }

        // Copy file into docker
        $codeFile = "code" . Language::getExtension($language);
        shell_exec("docker cp $codeFilePath " . self::CONTAINER . ":$dockerFolder/$codeFile");

        // Setup Command
        $compileCommand = Language::getCompileCommand($codeFilePath, $dockerFolder, $language);
//        $dockerCommand = "docker exec " . self::CONTAINER . " bash -c '$compileCommand'";

        $dockerCommand = 'docker exec ' . self::CONTAINER . ' bash -c ' . escapeshellarg($compileCommand);
        $descriptorspec = array(
            0 => array("pipe", "r"), // STDIN
            1 => array("pipe", "w"), // STDOUT
            2 => array("pipe", "w"), // STDERR
        );
        $process = proc_open($dockerCommand, $descriptorspec, $pipes);

        // COMPILING
        if (is_resource($process))
        {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $result = new CompileDto(Verdict::DEFAULT, "");

            if (proc_close($process) != 0)
            {
                $result->status = Verdict::COMPILE_ERROR;
                if (in_array($language, ["cpp", "java"]))
                {
                    try {
                        $result->message = self::filterCompilerError($stderr);
                    } catch (\Exception $ex) {
                        $out = strstr($stderr, Language::getExtension($language), ":");
                        $result->message = $out;
                    }
                }
                else if ($language == "pascal")
                {
                    try {
                        preg_match_all('/\w+\.pas\(\d+,\d+\) (?:Error|Fatal): (.+)$/m', $stdout, $matches);
                        $errors = $matches[1];
                        $result->message = implode("\n", $errors);
                    } catch (\Exception $x) {
                        $result->message = "Compile Error";
                    }
                }


                // Remove all files (we don't use anymore because it's compile error)
                $dockerCommand = "docker exec " . self::CONTAINER . " bash -c " . escapeshellarg("rm -rf $dockerFolder");
                shell_exec($dockerCommand);
            }

            return $result;
        }

        return new CompileDto(Verdict::COMPILE_ERROR, "Can't Compile the code");
    }

    public static function judge(string $inputFilePath, string $outputFilePath, string $dockerFolder, string $language, int $timeLimit, int $memoryLimit): JudgeDto
    {
        $result = new JudgeDto(Verdict::ACCEPTED, "", "", number_format($timeLimit, 3, ".", ""));
        $inputFileName = "input.in";
        $outputFileName = "result.out";

        // COPY INPUT FILE
        shell_exec("docker cp $inputFilePath " . self::CONTAINER . ":$dockerFolder/$inputFileName");

        $judgeCommand = Language::getJudgeCommand($dockerFolder, $language);
        $dockerCommand = "docker exec " . self::CONTAINER . " bash -c " . escapeshellarg("ulimit -St $timeLimit -Sm $memoryLimit ; $judgeCommand");

        // JUDGE
        $descriptorspec = array(
            0 => array("pipe", "r"),  // STDIN
            1 => array("pipe", "w"),  // STDOUT
            2 => array("pipe", "w"),  // STDERR
        );
        $process = proc_open($dockerCommand, $descriptorspec, $pipes);
        if (is_resource($process))
        {
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // CHECK VERDICT
            if (strstr($stderr, self::TIME_LIMIT_STRING) != null) $result->status = Verdict::TIME_LIMIT_EXCEEDED;
            if (strstr($stderr, self::MEMORY_LIMIT_STRING) != null) $result->status = Verdict::MEMORY_LIMIT_EXCEEDED;

            // TODO: STUCK DI SINI
            if (($result->status == Verdict::ACCEPTED) && (substr($stderr, 1, 4) != "real"))
            {
                $result->status = Verdict::RUNTIME_ERROR;
//                $result->stderr = $stderr;
//                $result->stderr = "TEST";
                if (in_array($language, ["cpp", "java"]))
                {
                    try {
                        $err = strstr($stderr, "real", true);
                        $tempErr = strstr($err, "(core dumped)", true);
                        $result->stderr = ($tempErr != null) ?
                            self::filterCompilerError($tempErr) :
                            self::filterCompilerError($err);
                    } catch (\Exception $x) {
                        $err = strstr($stderr, "real", true);
                        $tempErr = strstr($err, "(core dumped)", true);
                        $result->stderr = ($tempErr != null) ? $tempErr : $err;
                    }
                }
                else if ($language == 'pascal') {
                    try {
                        preg_match_all('/\w+\.pas\(\d+,\d+\) (?:Error|Fatal|Note): (.+)$/m', $stdout, $matches);
                        $errors = $matches[1];
                        $result->stderr = implode("\n", $errors);
                    } catch (\Exception $x) {
                        $result->stderr = "Run Time Error";
                    }
                }
            }

            // IF NOT ERROR, TAKE THE OUTPUT
            if ($result->status == Verdict::ACCEPTED)
            {
                $dockerCommand = "docker cp " . self::CONTAINER . ":$dockerFolder/$outputFileName $outputFilePath";
                shell_exec($dockerCommand);
                if ($res = fopen("$outputFilePath/$outputFileName", 'r'))
                {
                    $result->stdout = stream_get_contents($res);
                    fclose($res);
                }
            }

            // PARSE RUNTIME
            $result->runtime = self::parseRuntime($stderr);
            proc_close($process);
        }
        else
        {
            $result->status = Verdict::TIME_LIMIT_EXCEEDED;
        }

        // REMOVE ALL FILES
        $dockerCommand = "docker exec " . self::CONTAINER . " bash -c " . escapeshellarg("rm -rf $dockerFolder");
        shell_exec($dockerCommand);

        // RETURN
        return $result;
    }
}