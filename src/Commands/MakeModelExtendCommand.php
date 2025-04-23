<?php

namespace LaravelServiceGateway\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use function LaravelServiceGateway\Console\Commands\app_path;

class MakeModelExtendCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Model class along with Service and Gateway classes (optional)';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Models';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model class.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['service', 's', InputOption::VALUE_NONE, 'Generate a Service class for the model'],
            ['gateway', 'g', InputOption::VALUE_NONE, 'Generate a Gateway class for the model'],
        ];
    }

    /**
     * Qualify the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\');

        $rootNamespace = $this->rootNamespace();
        if (Str::startsWith($name, $rootNamespace)) {
            $name = Str::replaceFirst($rootNamespace, '', $name);
        }

        return $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . str_replace('/', '\\', $name);
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        return app_path(str_replace('\\', '/', $name) . '.php');
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());

        return str_replace(
            ['DummyNamespace', 'DummyClass'],
            [$this->getNamespace($name), class_basename($name)],
            $stub
        );
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        // First, generate the model class using the default make:model functionality
        parent::handle();

        // Optionally, create Service and Gateway if the corresponding flags are provided
        if ($this->option('service')) {
            $this->call('make:service', ['name' => $this->argument('name')]);
            $this->info('✔ Service created successfully.');
        }

        if ($this->option('gateway')) {
            $this->call('make:gateway', ['name' => $this->argument('name')]);
            $this->info('✔ Gateway created successfully.');
        }
    }
}
