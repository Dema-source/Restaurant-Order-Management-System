<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name : The name of the microservice (e.g. Product or Admin/Product)}
                            {--folder= : Optional folder path for Controller and Requests (e.g. Admin)}
                            {--m|migration : Create a migration file}';

    protected $description = 'Create a new microservice with Repository pattern (Model, Controller, Repository, Service, Requests, Resource, Routes)';

    protected Filesystem $files;

    protected array $replacements = [];

    protected string $folderPath = '';
    protected string $namespace = '';
    protected string $baseName = '';

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $this->parseFolderStructure();
        
        $name = Str::studly($this->baseName);
        $plural = Str::pluralStudly($name);
        $camel = Str::camel($name);
        $camelPlural = Str::camel($plural);
        $snake = Str::snake($name);
        $snakePlural = Str::snake($plural);

        $requestNamespace = $this->folderPath ? "{$this->folderPath}\\{$name}" : $name;
        
        $this->replacements = [
            '{{Name}}'              => $name,
            '{{Plural}}'            => $plural,
            '{{camel}}'             => $camel,
            '{{camelPlural}}'       => $camelPlural,
            '{{snake}}'             => $snake,
            '{{snakePlural}}'       => $snakePlural,
            '{{Namespace}}'         => $this->namespace,
            '{{FolderPath}}'        => $this->folderPath,
            '{{NamespacePrefix}}'   => $this->namespace ? "\\{$this->namespace}" : '',
            '{{RequestNamespace}}'  => $requestNamespace,
        ];

        $this->info("Creating microservice: {$name}");
        $this->newLine();

        $this->generateFile('model', app_path("Models/{$name}.php"), "Model [{$name}]");
        $this->generateFile('repository-interface', app_path("Repositories/Contracts/{$name}RepositoryInterface.php"), "Repository Interface [{$name}RepositoryInterface]");
        $this->generateFile('repository', app_path("Repositories/{$name}Repository.php"), "Repository [{$name}Repository]");
        $this->generateFile('service', app_path("Services/{$name}Service.php"), "Service [{$name}Service]");
        $requestPath = $this->folderPath ? "{$this->folderPath}/{$name}" : $name;
        $controllerPath = $this->folderPath ? "Api/{$this->folderPath}" : "Api";
        
        $this->generateFile('store-request', app_path("Http/Requests/{$requestPath}/Store{$name}Request.php"), "Request [Store{$name}Request]");
        $this->generateFile('update-request', app_path("Http/Requests/{$requestPath}/Update{$name}Request.php"), "Request [Update{$name}Request]");
        $this->generateFile('resource', app_path("Http/Resources/{$name}Resource.php"), "Resource [{$name}Resource]");
        $this->generateFile('controller', app_path("Http/Controllers/{$controllerPath}/{$name}Controller.php"), "Controller [{$name}Controller]");
        $this->generateRouteFile($snakePlural);
        $this->registerRoutes($snakePlural);
        $this->registerRepository($name);

        if ($this->option('migration') || $this->option('all')) {
            $this->createMigration($snakePlural);
        }


        $this->newLine();
        $this->info("Microservice [{$name}] created successfully!");
        $this->newLine();
        $requestPath = $this->folderPath ? "{$this->folderPath}/{$name}" : $name;
        $controllerPath = $this->folderPath ? "Api/{$this->folderPath}" : "Api";
        
        $this->table(
            ['Component', 'Path'],
            [
                ['Model', "app/Models/{$name}.php"],
                ['Repository Interface', "app/Repositories/Contracts/{$name}RepositoryInterface.php"],
                ['Repository', "app/Repositories/{$name}Repository.php"],
                ['Service', "app/Services/{$name}Service.php"],
                ['Controller', "app/Http/Controllers/{$controllerPath}/{$name}Controller.php"],
                ['Store Request', "app/Http/Requests/{$requestPath}/Store{$name}Request.php"],
                ['Update Request', "app/Http/Requests/{$requestPath}/Update{$name}Request.php"],
                ['Resource', "app/Http/Resources/{$name}Resource.php"],
                ['Routes', "routes/api/{$snakePlural}.php"],
            ]
        );

        return self::SUCCESS;
    }

    protected function getStubPath(string $stubName): string
    {
        return __DIR__ . "/stubs/service/{$stubName}.stub";
    }

    protected function getStubContent(string $stubName): string
    {
        $path = $this->getStubPath($stubName);

        if (!$this->files->exists($path)) {
            throw new \RuntimeException("Stub file not found: {$path}");
        }

        return str_replace(
            array_keys($this->replacements),
            array_values($this->replacements),
            $this->files->get($path)
        );
    }

    protected function generateFile(string $stubName, string $targetPath, string $label): void
    {
        if ($this->files->exists($targetPath)) {
            $this->warn("  {$label} already exists.");
            return;
        }

        $this->files->ensureDirectoryExists(dirname($targetPath));
        $this->files->put($targetPath, $this->getStubContent($stubName));
        $this->components->info("  {$label} created.");
    }

    protected function generateRouteFile(string $snakePlural): void
    {
        $routeDir = base_path('routes/api');
        $routePath = "{$routeDir}/{$snakePlural}.php";

        $this->files->ensureDirectoryExists($routeDir);

        if ($this->files->exists($routePath)) {
            $this->warn("  Route file already exists: routes/api/{$snakePlural}.php");
            return;
        }

        $this->files->put($routePath, $this->getStubContent('routes'));
        $this->components->info("  Routes [routes/api/{$snakePlural}.php] created.");
    }

    protected function registerRoutes(string $snakePlural): void
    {
        $apiRoutePath = base_path('routes/api.php');
        $requireLine = "require __DIR__.'/api/{$snakePlural}.php';";

        $content = $this->files->get($apiRoutePath);

        if (Str::contains($content, $requireLine)) {
            $this->warn("  Route already registered in api.php");
            return;
        }

        $this->files->append($apiRoutePath, "\n{$requireLine}\n");
        $this->components->info("  Route registered in [routes/api.php].");
    }

    protected function registerRepository(string $name): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (!$this->files->exists($providerPath)) {
            $this->warn("  RepositoryServiceProvider not found. Please register binding manually.");
            return;
        }

        $content = $this->files->get($providerPath);

        $interfaceClass = "\\App\\Repositories\\Contracts\\{$name}RepositoryInterface::class";
        $implementationClass = "\\App\\Repositories\\{$name}Repository::class";
        $binding = "        {$interfaceClass} => {$implementationClass},";

        if (Str::contains($content, $interfaceClass)) {
            $this->warn("  Repository binding already exists.");
            return;
        }

        $content = str_replace(
            "// \\App\\Repositories\\Contracts\\ExampleRepositoryInterface::class => \\App\\Repositories\\ExampleRepository::class,",
            "// \\App\\Repositories\\Contracts\\ExampleRepositoryInterface::class => \\App\\Repositories\\ExampleRepository::class,\n{$binding}",
            $content
        );

        $this->files->put($providerPath, $content);
        $this->components->info("  Repository binding registered in [RepositoryServiceProvider].");
    }

    protected function createMigration(string $table): void
    {
        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    protected function parseFolderStructure(): void
    {
        $nameArgument = $this->argument('name');
        $folderOption = $this->option('folder');

        if ($folderOption) {
            $this->folderPath = str_replace('/', '\\', trim($folderOption, '/\\'));
            $this->baseName = $nameArgument;
        } elseif (str_contains($nameArgument, '/') || str_contains($nameArgument, '\\')) {
            $parts = preg_split('/[\/\\\\]+/', $nameArgument);
            $this->baseName = array_pop($parts);
            $this->folderPath = implode('\\', $parts);
        } else {
            $this->baseName = $nameArgument;
            $this->folderPath = '';
        }

        $this->namespace = str_replace('/', '\\', $this->folderPath);
        
        if ($this->folderPath) {
            $this->info("Using folder structure: {$this->folderPath}");
        }
    }
}
