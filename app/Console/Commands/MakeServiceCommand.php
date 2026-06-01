<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * Artisan command to generate a complete microservice structure following the Repository pattern.
 *
 * This command automates the creation of all necessary files for a new microservice,
 * including:
 * - Model (Eloquent model)
 * - Repository Interface (contract)
 * - Repository (implementation)
 * - Service (business logic layer)
 * - Controller (API endpoints)
 * - Form Request classes (validation)
 * - API Resource (response transformation)
 * - Route file (API routes)
 *
 * Usage:
 *   php artisan make:service Category
 *   php artisan make:service Category --folder=Admin
 *   php artisan make:service Category --migration
 *
 */
class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name : The name of the microservice (e.g. Category or Admin/Category)}
                            {--folder= : Optional folder path for Controller and Requests (e.g. Admin)}
                            {--m|migration : Create a migration file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new microservice with Repository pattern (Model, Controller, Repository, Service, Requests, Resource, Routes)';

    /**
     * The filesystem instance for file operations.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * Array of placeholder replacements for stub templates.
     *
     * Keys are placeholder names (e.g., '{{Name}}'), values are the actual strings
     * to replace them with based on the service name and structure.
     *
     * @var array
     */
    protected array $replacements = [];

    /**
     * The folder path for organizing controllers and requests (e.g., 'Admin').
     *
     * @var string
     */
    protected string $folderPath = '';

    /**
     * The namespace prefix for the generated classes.
     *
     * @var string
     */
    protected string $namespace = '';

    /**
     * The base name of the service (e.g., 'Category' from 'Admin/Category').
     *
     * @var string
     */
    protected string $baseName = '';

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files The filesystem instance for file operations
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * This is the main entry point for the command. It orchestrates the entire
     * microservice generation process by:
     * 1. Parsing the folder structure from the input
     * 2. Preparing string replacements for stub templates
     * 3. Generating all necessary files (Model, Repository, Service, Controller, etc.)
     * 4. Registering routes and repository bindings
     * 5. Optionally creating a migration file
     * 6. Displaying a summary table of created files
     *
     * @return int Exit code (SUCCESS = 0)
     */
    public function handle(): int
    {
        // Parse the folder structure from the command arguments/options
        $this->parseFolderStructure();

        // Prepare various string formats of the service name for use in templates
        $name = Str::studly($this->baseName);
        $plural = Str::pluralStudly($name);
        $camel = Str::camel($name);
        $camelPlural = Str::camel($plural);
        $snake = Str::snake($name);
        $snakePlural = Str::snake($plural);

        // Determine the request namespace based on folder structure
        $requestNamespace = $this->folderPath ? "{$this->folderPath}\\{$name}" : $name;

        // Build the replacement array for stub template placeholders
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

        // Generate the core business logic files
        $this->generateFile('model', app_path("Models/{$name}.php"), "Model [{$name}]");
        $this->generateFile('repository-interface', app_path("Repositories/Contracts/{$name}RepositoryInterface.php"), "Repository Interface [{$name}RepositoryInterface]");
        $this->generateFile('repository', app_path("Repositories/{$name}Repository.php"), "Repository [{$name}Repository]");
        $this->generateFile('service', app_path("Services/{$name}Service.php"), "Service [{$name}Service]");

        // Determine paths for controllers and requests based on folder structure
        $requestPath = $this->folderPath ? "{$this->folderPath}/{$name}" : $name;
        $controllerPath = $this->folderPath ? "Api/{$this->folderPath}" : "Api";

        // Generate HTTP layer files
        $this->generateFile('store-request', app_path("Http/Requests/{$requestPath}/Store{$name}Request.php"), "Request [Store{$name}Request]");
        $this->generateFile('update-request', app_path("Http/Requests/{$requestPath}/Update{$name}Request.php"), "Request [Update{$name}Request]");
        $this->generateFile('resource', app_path("Http/Resources/{$name}Resource.php"), "Resource [{$name}Resource]");
        $this->generateFile('controller', app_path("Http/Controllers/{$controllerPath}/{$name}Controller.php"), "Controller [{$name}Controller]");

        // Register repository binding in the service provider
        $this->registerRepository($name);

        // Create migration if requested
        if ($this->option('migration') || $this->option('all')) {
            $this->createMigration($snakePlural);
        }

        $this->newLine();
        $this->info("Microservice [{$name}] created successfully!");
        $this->newLine();

        // Re-calculate paths for the summary table
        $requestPath = $this->folderPath ? "{$this->folderPath}/{$name}" : $name;
        $controllerPath = $this->folderPath ? "Api/{$this->folderPath}" : "Api";

        // Display a summary table of all created files
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
                // ['Routes', "routes/api/{$snakePlural}.php"],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Get the full path to a stub file.
     *
     * Stub files are template files used to generate the actual code files.
     * They are located in the stubs/service directory relative to this command.
     *
     * @param string $stubName The name of the stub file (without .stub extension)
     * @return string The absolute path to the stub file
     */
    protected function getStubPath(string $stubName): string
    {
        return __DIR__ . "/stubs/service/{$stubName}.stub";
    }

    /**
     * Get the content of a stub file with all placeholders replaced.
     *
     * This method reads the stub file and replaces all placeholder keys
     * (e.g., '{{Name}}', '{{Namespace}}') with their corresponding values
     * from the replacements array.
     *
     * @param string $stubName The name of the stub file to load
     * @return string The processed stub content with all replacements applied
     * @throws \RuntimeException If the stub file does not exist
     */
    protected function getStubContent(string $stubName): string
    {
        $path = $this->getStubPath($stubName);

        // Validate that the stub file exists before attempting to read it
        if (!$this->files->exists($path)) {
            throw new \RuntimeException("Stub file not found: {$path}");
        }

        // Replace all placeholder keys with their corresponding values
        return str_replace(
            array_keys($this->replacements),
            array_values($this->replacements),
            $this->files->get($path)
        );
    }

    /**
     * Generate a file from a stub template.
     *
     * This method creates a new file based on a stub template. It:
     * 1. Checks if the file already exists (skips if it does)
     * 2. Ensures the target directory exists
     * 3. Writes the processed stub content to the target file
     *
     * @param string $stubName The name of the stub template to use
     * @param string $targetPath The absolute path where the file should be created
     * @param string $label A human-readable label for the file (used in console output)
     * @return void
     */
    protected function generateFile(string $stubName, string $targetPath, string $label): void
    {
        // Skip generation if the file already exists to prevent overwrites
        if ($this->files->exists($targetPath)) {
            $this->warn("  {$label} already exists.");
            return;
        }

        // Ensure the parent directory exists before creating the file
        $this->files->ensureDirectoryExists(dirname($targetPath));

        // Write the processed stub content to the target file
        $this->files->put($targetPath, $this->getStubContent($stubName));
        $this->components->info("  {$label} created.");
    }

    /**
     * Register the repository binding in the RepositoryServiceProvider.
     *
     * This method automatically registers the repository interface and its
     * implementation in the service provider for dependency injection.
     * This follows the SOLID principles by allowing the application to depend
     * on abstractions rather than concrete implementations.
     *
     * @param string $name The studly case name of the resource (e.g., 'Category')
     * @return void
     */
    protected function registerRepository(string $name): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        // Check if the service provider exists
        if (!$this->files->exists($providerPath)) {
            $this->warn("  RepositoryServiceProvider not found. Please register binding manually.");
            return;
        }

        $content = $this->files->get($providerPath);

        // Build the fully qualified class names for the binding
        $interfaceClass = "\\App\\Repositories\\Contracts\\{$name}RepositoryInterface::class";
        $implementationClass = "\\App\\Repositories\\{$name}Repository::class";
        $binding = "        {$interfaceClass} => {$implementationClass},";

        // Skip if the binding already exists
        if (Str::contains($content, $interfaceClass)) {
            $this->warn("  Repository binding already exists.");
            return;
        }

        // Add the binding after the example binding line
        $content = str_replace(
            "// \\App\\Repositories\\Contracts\\ExampleRepositoryInterface::class => \\App\\Repositories\\ExampleRepository::class,",
            "// \\App\\Repositories\\Contracts\\ExampleRepositoryInterface::class => \\App\\Repositories\\ExampleRepository::class,\n{$binding}",
            $content
        );

        $this->files->put($providerPath, $content);
        $this->components->info("  Repository binding registered in [RepositoryServiceProvider].");
    }

    /**
     * Create a database migration file for the microservice.
     *
     * This method calls Laravel's built-in make:migration command to create
     * a migration file for the database table associated with this microservice.
     *
     * @param string $table The snake_case plural name of the table (e.g., 'categories')
     * @return void
     */
    protected function createMigration(string $table): void
    {
        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }

    /**
     * Parse the folder structure from the command arguments.
     *
     * This method determines the folder path and base name for the microservice
     * based on the command arguments and options. It supports two formats:
     * 1. Using the --folder option: php artisan make:service Category --folder=Admin
     * 2. Using slash notation: php artisan make:service Admin/Category
     *
     * The parsed values are used to organize controllers and requests into
     * subdirectories (e.g., Admin/CategoryController).
     *
     * @return void
     */
    protected function parseFolderStructure(): void
    {
        $nameArgument = $this->argument('name');
        $folderOption = $this->option('folder');

        // If --folder option is provided, use it explicitly
        if ($folderOption) {
            $this->folderPath = str_replace('/', '\\', trim($folderOption, '/\\'));
            $this->baseName = $nameArgument;
        }
        // If the name argument contains slashes, parse it as a path
        elseif (str_contains($nameArgument, '/') || str_contains($nameArgument, '\\')) {
            $parts = preg_split('/[\/\\\\]+/', $nameArgument);
            $this->baseName = array_pop($parts);
            $this->folderPath = implode('\\', $parts);
        }
        // Otherwise, use the name as-is with no folder structure
        else {
            $this->baseName = $nameArgument;
            $this->folderPath = '';
        }

        // Convert folder path to namespace format
        $this->namespace = str_replace('/', '\\', $this->folderPath);

        // Inform the user about the folder structure being used
        if ($this->folderPath) {
            $this->info("Using folder structure: {$this->folderPath}");
        }
    }
}
