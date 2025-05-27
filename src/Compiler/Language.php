<?php

namespace DockerMultiLangCompiler\Compiler;

class Language {
    const LANGUAGES = [
        "cpp", "java", "pascal",
//        "python", "js"
    ];

    private const MAP_EXTENSION = [
        "cpp" => ".cpp",
        "java" => ".java",
        "pascal" => ".pas",
//        "python" => ".py",
//        "js" => ".js"
    ];

    private const COMPILED = ["cpp", "java", "pascal"];

    public static function getExtension(string $lang): string
    {
        return self::MAP_EXTENSION[strtolower($lang)];
    }

    public static function isCompiledLanguage(string $lang): bool
    {
        return in_array(strtolower($lang), self::COMPILED);
    }

    public static function getCompileCommand(string $localPath, string $dockerFolder, string $lang): string
    {
        $codeFile = "code" . Language::getExtension($lang);
        switch (strtolower($lang))
        {
            case "cpp":
                return "clang++ $dockerFolder/$codeFile -o $dockerFolder/compiled_cpp";
                break;
            case "java":
                return "javac $dockerFolder/$codeFile -d $dockerFolder";
                break;
            case "pascal":
                return "fpc $dockerFolder/$codeFile -o'$dockerFolder/compiled_pascal'";
                break;
            default:
                return "";
                break;
        }
    }

    public static function getJudgeCommand(string $dockerFolder, string $lang): string
    {
        switch (strtolower($lang))
        {
            case "cpp":
                return "time $dockerFolder/compiled_cpp < $dockerFolder/input.in > $dockerFolder/result.out";
                break;
            case "java":
                return "time java -cp $dockerFolder Main < $dockerFolder/input.in > $dockerFolder/result.out";
                break;
            case "pascal":
                return "time $dockerFolder/compiled_pascal < $dockerFolder/input.in > $dockerFolder/result.out";
            default:
                return "";
        }
    }
}