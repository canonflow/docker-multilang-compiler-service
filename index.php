<?php

require_once "./vendor/autoload.php";

use DockerMultiLangCompiler\Routes\Router;
use DockerMultiLangCompiler\Controllers\JudgeController;
use DockerMultiLangCompiler\Controllers\LanguageController;

$router = new Router();

$router->get('/', [JudgeController::class, 'index']);
$router->post('/judge', [JudgeController::class, 'judge']);

// Language
$router->get('/languages', [LanguageController::class, 'get']);

echo $router->resolve();