<?php

namespace DockerMultiLangCompiler\Dto;

class JudgeDto
{
    public $status;
//    public $message;
    public $stderr;
    public $stdout;
    public $runtime;

    public function __construct(string $status, string $stdout, string $stderr, string $runtime)
    {
        $this->status = $status;
//        $this->message = $message;
        $this->stderr = $stderr;
        $this->stdout = $stdout;
        $this->runtime = $runtime;
    }
}