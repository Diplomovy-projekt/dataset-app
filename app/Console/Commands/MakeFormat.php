<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class MakeFormat extends Command
{
    protected $signature = 'make:format {name} {--delete}';
    protected $description = 'Generate files for a new annotation format';
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): void
    {
        if ($this->option('delete')) {
            $this->deleteFormat();
            return;
        }

        $name = ucfirst(strtolower($this->argument('name'))); // Normalize format name

        $filesToGenerate = [
            "Configs/Annotations/{$name}Config.php" => $this->getConfigTemplate($name),
            "ExportService/Mappers/To{$name}.php"   => $this->getMapperTemplate($name, 'To', 'App\\ExportService\\Mappers\\BaseToMapper'),
            "ImportService/Mappers/From{$name}.php" => $this->getMapperTemplate($name, 'From', 'App\\ImportService\\Mappers\\BaseFromMapper'),
            "ImportService/Validators/{$name}/{$name}AnnotationValidator.php" => $this->getAnnotationValidatorTemplate($name),
            "ImportService/Validators/{$name}/{$name}ZipValidator.php"        => $this->getZipValidatorTemplate($name),
        ];

        $formatExists = false;
        foreach ($filesToGenerate as $path => $content) {
            $fullPath = app_path($path);

            if (!$this->files->exists($fullPath)) {
                $this->files->ensureDirectoryExists(dirname($fullPath));
                $this->files->put($fullPath, $content);
                $this->info("Created: {$path}");
            } else {
                $this->warn("Skipped (already exists): {$path}");
                $formatExists = true;
            }
        }

        if (!$formatExists) {
            $this->info("Format {$name} created successfully!");
            $this->line("Note: Please check the abstract base classes for documentation on methods that need to be implemented:");
            $this->line(" - BaseAnnotationConfig: Required constants for annotation structure");
            $this->line(" - BaseToMapper: Methods for mapping to the {$name} format");
            $this->line(" - BaseFromMapper: Methods for mapping from the {$name} format");
            $this->line(" - Base validators: Methods for validating {$name} annotation files");
        }
    }

    private function getConfigTemplate($name) {
        return "<?php

namespace App\\Configs\\Annotations;

use App\\Configs\\Annotations\\BaseAnnotationConfig;

/**
 * Configuration for {$name} annotation format
 *
 * Override the constants from BaseAnnotationConfig that are relevant for this format.
 * Required constants that must be defined: LABEL_EXTENSION
 * @see \\App\\Configs\\Annotations\\BaseAnnotationConfig For documentation on all available constants
 */
class {$name}Config extends BaseAnnotationConfig
{
    /*
     * Expected archive structure for {$name}:
     *
     * EXAMPLE:
     * root_folder/
     * ├── images/
     * │   ├── image1.jpg
     * │   └── image2.jpg
     * └── labels/
     *     ├── image1.json   <-- Define LABEL_EXTENSION constant
     *     └── image2.json
     */

    // Labels
    public const LABEL_EXTENSION = '';
    // public const LABELS_FILE = 'filename.ext'; // Define if your format has a specific file for labels
    // public const LABELS_FOLDER = 'labels';     // Define if your format has a specific file for labels

    // Images
    // public const IMAGE_FOLDER = 'images';      // Define if your format has a specific file for images
}";
    }

    private function getMapperTemplate($name, $prefix, $baseClass)
    {
        $className = "{$prefix}{$name}";
        $namespace = "App\\" . ($prefix === 'To' ? "ExportService\\Mappers" : "ImportService\\Mappers");
        $baseClassName = class_basename($baseClass);

        // Fetch required abstract methods dynamically
        $methods = $this->getAbstractMethods($baseClass);

        return "<?php

namespace {$namespace};

use {$baseClass};

/**
 * {$className} mapper for {$name} format
 *
 * This class is responsible for mapping " . ($prefix === 'To' ? "internal representation to {$name}" : "{$name} format to internal representation") . " format.
 * @see \\{$baseClass} For documentation on required methods to implement
 */
class {$className} extends {$baseClassName}
{
{$methods}}";
    }

    private function getAnnotationValidatorTemplate($name)
    {
        return "<?php

namespace App\\ImportService\\Validators\\{$name};

use App\\ImportService\\Validators\\BaseValidator\\BaseAnnotationValidator;

/**
 * Validator for {$name} annotation files
 *
 * This class is responsible for validating the structure and content of {$name} annotation files.
 * @see \\App\\ImportService\\Validators\\BaseValidator\\BaseAnnotationValidator For common validation methods
 */
class {$name}AnnotationValidator
{
    /**
     * Validate annotation data for {$name} format
     *
     * @param mixed \$data The annotation data to validate
     * @return bool|array Returns true if valid, or array of error messages if invalid
     */
    public static function validate(\$data)
    {
        // Implement annotation validation for {$name} format
    }
}";
    }

    private function getZipValidatorTemplate($name)
    {
        return "<?php

namespace App\\ImportService\\Validators\\{$name};

use App\\ImportService\\Validators\\BaseValidator\\BaseZipValidator;

/**
 * Validator for {$name} ZIP archive files
 *
 * This class is responsible for validating the structure and content of {$name} ZIP archives.
 * @see \\App\\ImportService\\Validators\\BaseValidator\\BaseZipValidator For common ZIP validation methods
 */
class {$name}ZipValidator
{
    /**
     * Validate ZIP file for {$name} format
     *
     * @param string \$zipFile Path to the ZIP file to validate
     * @return bool|array Returns true if valid, or array of error messages if invalid
     */
    public static function validate(\$zipFile)
    {
        // Implement ZIP file validation for {$name} format
    }
}";
    }

    /**
     * Fetches all abstract methods from a given base class and generates method stubs.
     */
    private function getAbstractMethods($baseClass)
    {
        try {
            $reflection = new ReflectionClass($baseClass);
            $methods = [];

            foreach ($reflection->getMethods(ReflectionMethod::IS_ABSTRACT) as $method) {
                $params = collect($method->getParameters())->map(function ($param) {
                    $type = $param->hasType() ? $param->getType()->getName() : '';
                    $type = class_exists($type) ? '\\' . $type . ' ' : ($type ? $type . ' ' : '');
                    $default = $param->isOptional() ? ' = ' . var_export($param->getDefaultValue(), true) : '';
                    return $type . '$' . $param->getName() . $default;
                })->implode(', ');

                $returnType = '';
                if ($method->hasReturnType()) {
                    $returnTypeType = $method->getReturnType()->getName();
                    $returnType = class_exists($returnTypeType) ? ': \\' . $returnTypeType : ': ' . $returnTypeType;
                }

                $methods[] = "    /**\n     * Implementation of {$method->getName()} from {$baseClass}\n     *\n     * @see \\{$baseClass}::{$method->getName()} For method documentation\n     */\n    public function {$method->getName()}({$params}){$returnType}\n    {\n        // Implement {$method->getName()} logic\n    }\n";
            }

            return implode("\n", $methods) . "\n";
        } catch (\ReflectionException $e) {
            return "    // Error fetching methods from {$baseClass}: {$e->getMessage()}\n";
        }
    }

    private function deleteFormat(): void
    {
        $name = ucfirst(strtolower($this->argument('name')));

        $filesToDelete = [
            "Configs/Annotations/{$name}Config.php",
            "ExportService/Mappers/To{$name}.php",
            "ImportService/Mappers/From{$name}.php",
            "ImportService/Validators/{$name}/{$name}AnnotationValidator.php",
            "ImportService/Validators/{$name}/{$name}ZipValidator.php",
        ];

        foreach ($filesToDelete as $path) {
            $fullPath = app_path($path);
            if ($this->files->exists($fullPath)) {
                $this->files->delete($fullPath);
                $this->info("Deleted: {$path}");
            }
        }

        // Remove empty folders if they exist
        $this->files->deleteDirectory(app_path("ImportService/Validators/{$name}"));

        $this->info("Format {$name} deleted successfully.");
    }
}
