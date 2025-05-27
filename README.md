# Docker MultiLang Compiler Service
A PHP-based service for executing and compiling code in multiple programming languages using Docker container.

## Features
- Isolated and secure execution using Docker
- Designed as a backend service (REST-compatible)
- Easy to extend with new language support
- Suitable for online judges, code runners, and educational platforms

## Supported Languages
- C++ (**clang17++**)
- Java (**Java 17.0.1**)
- Pascal (**Free Pascal Compiler 3.2.0**)
- Python **(soon)**
- JS **(NodeJS - soon)**

## Technologies Used
- **PHP** (Core logic / service layer)
- **Docker** (Sandboxes code execution)
- **Unix** / **Alpine Linux** environment (CLI based compilation / execution)

## Folder Structure
```
.
├── docker/
│   ├── compiler.dockerfile
│   └── sandbox/
├── src/
│   ├── Compiler/
│   │   └── Language.php
│   ├── Controllers/
│   │   └── JudgeController.php
│   │   └── LanguageController.php
│   ├── Dto/
│   │   └── CompileDto.php
│   │   └── JudgeDto.php
│   ├── Helper/
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── TempPath.php
│   │   ├── Validator.php
│   │   └── Verdict.php
│   ├── Routes/
│   │   └── Router.php
│   └── Services/
│       └── JudgeService.php
├── temp/
├── vendor/
├── .gitignore
├── composer.json
├── composer-lock.json
├── docker-compose.yml
├── index.php
├── LICENSE
└── README.md
```

## How It Works
1. Receives code, language, and option input via HTTP
2. Spins up a Docker container with volume bind
3. Compiles / executes code inside container
4. Returns stdout, stderr, verdict, and runtime.

## Example API
### Request
```http
POST /judge
{
    "language" : "string",
    "code": "string",
    "input": "string",
    "time_limit": "integer",
    "memory_limit" "integer",
}
```
### Response
```json
{
  "status": "Ok",
  "code": 200,
  "data": {
    "stdout": "string",
    "stderr": "string",
    "verdict": "string",
    "runtime": "string"
  }
}
```

## Setup Instructions
### Requirements
- Docker
- PHP 8.x
- Composer