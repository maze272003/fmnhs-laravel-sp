<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class VerifyRouteContracts extends Command
{
    protected $signature = 'route:verify-contracts';
    protected $description = 'Verify that all routes have matching controller methods';

    protected array $errors = [];
    protected array $warnings = [];

    public function handle(Router $router): int
    {
        $this->info('Verifying route-controller contracts...');

        $routes = $router->getRoutes();

        foreach ($routes as $route) {
            $this->verifyRoute($route);
        }

        $this->outputResults();

        return count($this->errors) > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function verifyRoute(Route $route): void
    {
        $controller = $route->getControllerClass();

        if (!$controller) {
            return;
        }

        if (!class_exists($controller)) {
            $this->errors[] = "Controller class not found: {$controller} for route " . $route->uri();
            return;
        }

        $method = $route->getActionMethod();

        if (!$method || $method === 'missing') {
            return;
        }

        $reflection = new ReflectionClass($controller);

        if (!$reflection->hasMethod($method)) {
            $this->errors[] = "Method {$method}() not found in {$controller} for route " . $route->uri();
            return;
        }

        $this->verifyMethodSignature($reflection->getMethod($method), $route);
    }

    protected function verifyMethodSignature(ReflectionMethod $method, Route $route): void
    {
        $parameters = $method->getParameters();
        $routeParameters = $route->parameterNames();

        foreach ($routeParameters as $paramName) {
            $found = false;
            foreach ($parameters as $param) {
                if ($param->getName() === $paramName || $param->getName() === 'id') {
                    $found = true;
                    break;
                }
                $type = $param->getType();
                if ($type && !$type->isBuiltin()) {
                    $typeName = $type->getName();
                    if (class_exists($typeName)) {
                        $modelClass = new ReflectionClass($typeName);
                        if (strtolower($modelClass->getShortName()) === strtolower($paramName)) {
                            $found = true;
                            break;
                        }
                    }
                }
            }

            if (!$found) {
                $this->warnings[] = "Route parameter '{$paramName}' may not match method signature in " .
                    $method->getDeclaringClass()->getName() . "::{$method->getName()}() for route " . $route->uri();
            }
        }
    }

    protected function outputResults(): void
    {
        $this->newLine();

        if (count($this->errors) > 0) {
            $this->error('ERRORS FOUND:');
            foreach ($this->errors as $error) {
                $this->line("  <fg=red>✗</> {$error}");
            }
        }

        if (count($this->warnings) > 0) {
            $this->newLine();
            $this->warn('WARNINGS:');
            foreach ($this->warnings as $warning) {
                $this->line("  <fg=yellow>!</> {$warning}");
            }
        }

        if (count($this->errors) === 0 && count($this->warnings) === 0) {
            $this->info('All route-controller contracts are valid!');
        }

        $this->newLine();
        $this->info("Summary: " . count($this->errors) . " errors, " . count($this->warnings) . " warnings");
    }
}
