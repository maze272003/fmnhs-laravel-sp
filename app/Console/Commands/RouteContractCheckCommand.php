<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionClass;
use ReflectionMethod;

class RouteContractCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:check-contracts {--fix : Show suggestions for fixing issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that all routes have corresponding controller methods (CI static analysis)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking route-controller contracts...');
        $this->newLine();

        $routes = RouteFacade::getRoutes();
        $issues = [];
        $checked = 0;

        foreach ($routes as $route) {
            $controller = $route->getControllerClass();

            if (!$controller) {
                continue;
            }

            $method = $route->getActionMethod();

            if (!$method || $method === 'missing') {
                continue;
            }

            $checked++;

            if (!class_exists($controller)) {
                $issues[] = [
                    'route' => $route->uri(),
                    'type' => 'missing_controller',
                    'message' => "Controller class not found: {$controller}",
                    'suggestion' => "Create the controller: php artisan make:controller {$controller}",
                ];
                continue;
            }

            try {
                $reflection = new ReflectionClass($controller);

                if (!$reflection->hasMethod($method)) {
                    $issues[] = [
                        'route' => $route->uri(),
                        'type' => 'missing_method',
                        'message' => "Method '{$method}' not found in {$controller}",
                        'suggestion' => $this->suggestMethodFix($reflection, $method),
                    ];
                }
            } catch (\ReflectionException $e) {
                $issues[] = [
                    'route' => $route->uri(),
                    'type' => 'reflection_error',
                    'message' => "Could not reflect controller: {$controller}",
                    'suggestion' => 'Check if the controller file exists and is properly namespaced.',
                ];
            }
        }

        $this->displayResults($issues, $checked);

        return count($issues) > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Suggest a fix for a missing method.
     */
    private function suggestMethodFix(ReflectionClass $reflection, string $missingMethod): string
    {
        $existingMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn ($m) => !$m->isStatic() && $m->getDeclaringClass()->getName() === $reflection->getName())
            ->map(fn ($m) => $m->getName())
            ->values()
            ->toArray();

        // Find similar method names
        $similar = $this->findSimilarMethod($missingMethod, $existingMethods);

        if ($similar) {
            return "Did you mean '{$similar}'? Or add method '{$missingMethod}' to the controller.";
        }

        return "Add method '{$missingMethod}' to the controller.";
    }

    /**
     * Find a similar method name using Levenshtein distance.
     */
    private function findSimilarMethod(string $missing, array $existing): ?string
    {
        $shortest = PHP_INT_MAX;
        $closest = null;

        foreach ($existing as $method) {
            $distance = levenshtein($missing, $method);

            if ($distance < $shortest && $distance <= 4) {
                $shortest = $distance;
                $closest = $method;
            }
        }

        return $closest;
    }

    /**
     * Display the results.
     */
    private function displayResults(array $issues, int $checked): void
    {
        if (empty($issues)) {
            $this->info("✓ All {$checked} route-controller contracts are valid.");
            return;
        }

        $this->error("Found " . count($issues) . " issue(s) in {$checked} routes:");
        $this->newLine();

        foreach ($issues as $index => $issue) {
            $this->line(sprintf('<fg=red>%d.</> Route: <fg=yellow>%s</>', $index + 1, $issue['route']));
            $this->line(sprintf('   <fg=red>✗</> %s', $issue['message']));

            if ($this->option('fix') && isset($issue['suggestion'])) {
                $this->line(sprintf('   <fg=green>→ Suggestion:</> %s', $issue['suggestion']));
            }

            $this->newLine();
        }

        if (!$this->option('fix')) {
            $this->comment('Run with --fix to see suggestions for resolving issues.');
        }
    }
}
