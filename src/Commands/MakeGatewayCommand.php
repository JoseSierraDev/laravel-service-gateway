<?php

namespace LaravelServiceGateway\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use function LaravelServiceGateway\Console\Commands\app_path;

class MakeGatewayCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:gateway';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Gateway class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Gateway';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/gateway.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Gateways';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the gateway class.'],
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
        $result = parent::handle();

        if ($result !== false) {
            $this->info('✔ Gateway created successfully.');
        }

        return $result;
    }
}
