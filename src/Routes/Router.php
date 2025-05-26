<?php

namespace DockerMultiLangCompiler\Routes;

//use DockerMultiLang\Controllers\JudgeController;

class Router {
    private array $routes = [];

    public function get(string $path, callable|array $callback): void
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, callable|array $callback): void
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve(): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER["REQUEST_URI"] ?? '/';
        $path = explode('?', $path)[0];

        $callback = $this->routes[$method][$path] ?? null;

        if (is_null($callback))
        {
            http_response_code(404);
            return "404 Not Found";
        }

        if (is_array($callback))
        {
            [$class, $method] = $callback;
            $controller = new $class();
            return $controller->$method();
        }

        return $callback();
    }
}

