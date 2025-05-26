<?php

namespace DockerMultiLangCompiler\Services;

use DockerMultiLangCompiler\Compiler\Language;
use DockerMultiLangCompiler\Dto\CompileDto;
use DockerMultiLangCompiler\Helper\Response;

class JudgeService {

    private const USER = "executor";
    private const CONTAINER = "docker_compiler";

    private function filterCompilerError($errorMessage) {
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

    public function parseRuntime($stream)
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
        $m = substr($str, 5, $im - 5);
        $s = substr($str, $im + 1, $is - $im - 1);
        $runtime = number_format($m * 60 + $s, 3);
        return $runtime;
    }

    private static function mkdir()
    {
        $random = bin2hex(random_bytes(8));
        $folder = "/home/" . self::USER . "/workspace/" . $random;

        $command = "docker exec " . self::CONTAINER . " bash -c 'mkdir -p $folder && rm -rf $folder/*'";
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
    public static function compile($localPath, $dockerFolder, $language): CompileDto
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
        shell_exec("docker cp $localPath " . self::CONTAINER . ":$dockerFolder/$codeFile");

        // Setup Command
        $compileCommand = Language::getCompileCommand($localPath, $dockerFolder, $language);
        $dockerCommand = "docker exec " . self::CONTAINER . " bash -c '$compileCommand'";
        $descriptorspec = array(
            0 => array("pipe", "r"), // STDIN
            1 => array("pipe", "2"), // STDOUT
            2 => array("pipe", "2"), // STDERR
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
                $dockerCommand = "docker exec " . self::CONTAINER . " bash -c 'rm -rf $dockerFolder'";
            }

            return $result;
        }

        return new CompileDto(Verdict::COMPILE_ERROR, "Can't Compile the code");
    }
}