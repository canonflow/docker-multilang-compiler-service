<?php

namespace DockerMultiLangCompiler\Helper;

class Verdict {
    public const ACCEPTED = "accepted";
    public const WRONG_ANSWER = "wrong answer";
    public const COMPILE_ERROR = "compile error";
    public const RUNTIME_ERROR = "runtime error";
    public const TIME_LIMIT_EXCEEDED = "time limit exceeded";
    public const MEMORY_LIMIT_EXCEEDED = "memory limit exceeded";
    public const DEFAULT = "default";
}
