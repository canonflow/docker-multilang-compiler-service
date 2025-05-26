<?php

require_once "./vendor/autoload.php";

use DockerMultiLangCompiler\Routes\Router;
use DockerMultiLangCompiler\Controllers\JudgeController;

$router = new Router();

$router->get('/', [JudgeController::class, 'index']);
$router->post('/execute', [JudgeController::class, 'judge']);

echo $router->resolve();