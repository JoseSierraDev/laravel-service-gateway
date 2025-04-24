<?php

namespace LaravelServiceGateway\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeModelExtendCommand extends ModelMakeCommand
{
    /**
     * El nombre del comando.
     *
     * @var string
     */
    protected $name = 'make:model';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        parent::handle();

        $name = $this->qualifyClass($this->getNameInput());

        if ($this->option('service')) {
            $this->call('make:service', [
                'name' => $this->resolveLayerName($name, 'Service'),
            ]);
            $this->components->info('Service created successfully.');
        }

        if ($this->option('gateway')) {
            $this->call('make:gateway', [
                'name' => $this->resolveLayerName($name, 'Gateway'),
            ]);
            $this->components->info('Gateway created successfully.');
        }
    }

    /**
     * Resuelve el nombre del Service o Gateway basado en el modelo.
     */
    protected function resolveLayerName(string $modelName, string $type): string
    {
        $baseName = class_basename($modelName);
        $namespace = Str::replaceFirst($this->laravel->getNamespace(), '', $modelName);
        $folder = str_replace('\\' . $baseName, '', $namespace);

        return ($folder ? $folder . '/' : '') . $baseName . $type;
    }

    /**
     * Agrega las opciones de línea de comandos.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['service', '-serv', InputOption::VALUE_NONE, 'Create a new Service class for the model.'],
            ['gateway', 'g', InputOption::VALUE_NONE, 'Create a new Gateway class for the model.'],
        ]);
    }
}
