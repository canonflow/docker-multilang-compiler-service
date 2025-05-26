# Docker MultiLang Compiler Service
A PHP-based service for executing and compiling code in multiple programming languages using Docker container.

## Features
- Isolated and secure execution using Docker
- Designed as a backend service (REST-compatible)
- Easy to extend with new language support
- Suitable for online judges, code runners, and educational platforms

## Supported Languages
- C++ (CPP)
- Java
- Pascal
- Python
- JS (NodeJS)

## Technologies Used
- **PHP** (Core logic / service layer)
- **Docker** (Sandboxes code execution)
- **Unix** / Linux environment (CLI based compilation / execution)

## Folder Structure
```
.
├── docker/
│   ├── compiler.dockerfile
│   └── sandbox/
├── src/
│   ├── Compiler/
│   │   ├── Compiler.php
│   │   └── Language.php
│   ├── Controllers/
│   │   └── JudgeController.php
│   ├── Helper/
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Validator.php
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
2. Selects appropriate Docker image based on language
3. Spins up a Docker container with volume bind
4. Compiles / executes code inside container
5. Returns stdout, stderr, verdict, and runtime.

## Example API
### Request
```http
POST /execute
{
    "language" : "string",
    "code": "BlobFile",
    "input": "BlobFile",
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
    "runtime": "integer"
  }
}
```

## Setup Instructions
### Requirements
- Docker
- PHP 8.x
- Composer