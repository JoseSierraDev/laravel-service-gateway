<?php

namespace LaravelServiceGateway\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeValidatorCommand extends GeneratorCommand
{
    protected $name = 'make:validator';
    protected $description = 'Create a new Validator class';
    protected $type = 'Validator';

    protected $nameSpace = '';

    protected function getStub()
    {
        return __DIR__ . '/../../stubs/validator.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Validators';
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the validator class'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['formRequest', 'f', InputOption::VALUE_NONE, 'Also create a FormRequest class for this validator'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate validation rules based on the model'],
        ];
    }

    protected function replaceClass($stub, $name)
    {
        $class = class_basename($name);
        $stub = str_replace(['{{ class }}', '{{class}}'], $class, $stub);
        $stub = str_replace('{{ validatorClass }}', $class, $stub);
        $stub = str_replace('{{ rules }}', $this->generateRules($name), $stub);
        return $stub;
    }

    protected function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());
        $this->replaceNamespace($stub, $name);
        return $this->replaceClass($stub, $name);
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $this->namespace = Str::beforeLast($name, '\\');
        $stub = str_replace(['{{ namespace }}', '{{namespace}}'], $this->namespace, $stub);
    }

    protected function generateRules($name)
    {
        if (!$this->option('model')) {
            return '// No model provided';
        }

        $modelInput = $this->option('model');
        $baseModelNamespace = config('modules.model_namespace', 'App\\Models');

        if (!Str::contains($modelInput, '\\')) {
            foreach (['App\\Models\\' . $modelInput, 'App\\' . $modelInput] as $candidate) {
                if (class_exists($candidate)) {
                    $baseModelNamespace = Str::beforeLast($candidate, '\\');
                    break;
                }
            }
            $modelClass = $baseModelNamespace . '\\' . $modelInput;
        } else {
            $modelClass = $modelInput;
        }

        if (!class_exists($modelClass)) {
            return "// Model class '$modelClass' not found";
        }

        $model = new $modelClass();


        // ----- SCHEMA
        $table = $table = Str::afterLast($model->getTable(), '.');
        $schema = Schema::connection($model->getConnectionName());

        if (!$schema->hasTable($table)) {
            //return "// Table '$table' for model '$modelClass' not found";
            $this->warn('Schema table "' . $table . '" not found. Using fillables from model.');
            return $this->generateRulesFromFillables($model);
        }

        $columns = $schema->getColumnListing($table);
        $connection = $schema->getConnection();
        $doctrineSchema = $connection->getDoctrineSchemaManager();
        $doctrineColumns = $doctrineSchema->listTableColumns($table);
        $foreignKeys = $doctrineSchema->listTableForeignKeys($table);

        $indexes = $doctrineSchema->listTableIndexes($table);

        $rules = [];

        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $isNullable = isset($doctrineColumns[$column]) && !$doctrineColumns[$column]->getNotnull();
            $isUnique = collect($indexes)->contains(fn ($index) =>
                $index->isUnique() && in_array($column, $index->getColumns())
            );

            $ruleParts = [];
            $ruleParts[] = $isNullable ? "'nullable'" : "'required'";

            // Prioridad: cast > tipo BD
            $type = $casts[$column] ?? DB::getSchemaBuilder()->getColumnType($table, $column);

            switch ($type) {
                case 'string':
                    $length = isset($doctrineColumns[$column]) ? $doctrineColumns[$column]->getLength() : 255;
                    $ruleParts[] = "'string'";
                    $ruleParts[] = "'max:{$length}'";
                    break;
                case 'int':
                case 'integer':
                case 'bigint':
                    $ruleParts[] = "'integer'";
                    break;
                case 'boolean':
                    $ruleParts[] = "'boolean'";
                    break;
                case 'float':
                case 'double':
                case 'decimal':
                    $ruleParts[] = "'numeric'";
                    break;
                case 'date':
                case 'datetime':
                    $ruleParts[] = "'date'";
                    break;
                case 'array':
                    $ruleParts[] = "'array'";
                    break;
            }

            // Validación de clave foránea
            if ($foreignKey = $this->getForeignKey($foreignKeys, $column)) {
                list($referenceTable, $referenceColumn) = $foreignKey;

                $ruleParts[] = "'exists:$referenceTable,$referenceColumn'"; // Usamos el campo correcto de clave foránea
            }

            if ($isUnique) {
                $ruleParts[] = "'unique:$table,$column'";
            }

            $rules[] = "'$column' => [" . implode(', ', $ruleParts) . "]";
        }

        return implode(",\n            ", $rules);
    }


    protected function generateRulesFromFillables($model)
    {
        $casts = $model->getCasts();


            // Obtener relaciones del modelo

            $rules = [];

            foreach ($model->getFillable() as $column) {
                $ruleParts = [];

                $ruleParts[] = "'required'";

                // Detecta el tipo por casts
                $type = $casts[$column] ?? null;

                switch ($type) {
                    case 'string':
                        $ruleParts[] = "'string'";
                        $ruleParts[] = "'max:255'";
                        break;
                    case 'integer':
                    case 'int':
                        $ruleParts[] = "'integer'";
                        break;
                    case 'boolean':
                        $ruleParts[] = "'boolean'";
                        break;
                    case 'float':
                    case 'decimal':
                    case 'double':
                        $ruleParts[] = "'numeric'";
                        break;
                    case 'array':
                        $ruleParts[] = "'array'";
                        break;
                    case 'date':
                    case 'datetime':
                        $ruleParts[] = "'date'";
                        break;
                    default:
                        // Si no hay cast, no añade tipo
                        break;
                }


                $rules[] = "'$column' => [" . implode(', ', $ruleParts) . "]";
            }

            return implode(",\n            ", $rules);

    }


    public function handle()
    {
        parent::handle();

        if ($this->option('formRequest')) {
            $name = $this->argument('name');
            $formRequestClass = $name . 'Request';
            $validatorClass = class_basename($name);

            Artisan::call('make:request', ['name' => $formRequestClass]);

            $requestPath = app_path('Http/Requests/' . $formRequestClass . '.php');

            if (File::exists($requestPath)) {
                $content = File::get($requestPath);
                $className = '\\'.$this->namespace.'\\'.$validatorClass;
                $replaceRules = "return {$className}::rules();";
                $content = preg_replace("/public function rules\(\)\s*{[^}]*}/s", "public function rules()\n    {\n        {$replaceRules}\n    }", $content);

                File::put($requestPath, $content);
            }

            $this->info("FormRequest {$formRequestClass} created and linked to validator.");
        }
    }


    /**
     * Obtener el nombre de la tabla relacionada para una relación
     */
    protected function getRelationTable($model, $relationMethod)
    {
        $relation = $model->{$relationMethod}();
        return $relation->getRelated()->getTable();
    }

    /**
     * Verificar si una columna es una clave foránea
     */
    protected function getForeignKey($foreignKeys, $column)
    {
        foreach ($foreignKeys as $foreignKey) {
            if (in_array($column, $foreignKey->getColumns())) {
                // En lugar de getForeignTableName(), usamos getTable() y getForeignColumns()
                $referenceTable = $foreignKey->getForeignTableName();
                $referenceColumn = $foreignKey->getForeignColumns()[0]; // Obtenemos la columna de la clave foránea
                return [$referenceTable, $referenceColumn];
            }
        }

        return null;
    }
}
