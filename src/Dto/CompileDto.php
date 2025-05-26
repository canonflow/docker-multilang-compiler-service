<?php

namespace DockerMultiLangCompiler\Dto;

class CompileDto {
    public $status;
    public $message;

    public function __construct(string $status, string $message)
    {
        $this->status = $status;
        $this->message = $message;
    }
}

