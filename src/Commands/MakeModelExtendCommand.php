<?php

namespace LaravelServiceGateway\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MakeModelExtendCommand extends \Illuminate\Foundation\Console\ModelMakeCommand
{
    protected function configure()
    {
        parent::configure();

        $this->getDefinition()->addOptions([
            new InputOption('service', 'S', InputOption::VALUE_NONE, 'Create a new Service class for the model'),
            new InputOption('gateway', 'g', InputOption::VALUE_NONE, 'Create a new Gateway class for the model'),
            new InputOption('sg', null, InputOption::VALUE_NONE, 'Create both Service and Gateway classes for the model'),
            new InputOption('gs', null, InputOption::VALUE_NONE, 'Create both Service and Gateway classes for the model'),
        ]);
    }

    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        $exist = file_exists($path);

        parent::handle();

        if (!$exist) {
            $createService = false;
            $createGateway = false;

            if ($this->option('sg') || $this->option('gs')) {
                $createService = true;
                $createGateway = true;
            } else {
                $createService = $this->option('service');
                $createGateway = $this->option('gateway');
            }

            if ($createService) {
                $this->call('make:service', [
                    'name' => $this->resolveLayerName($name, 'Service'),
                ]);
            }

            if ($createGateway) {
                $this->call('make:gateway', [
                    'name' => $this->resolveLayerName($name, 'Gateway'),
                ]);
            }
        }
    }


    protected function resolveLayerName($name, $suffix)
    {
        $class = class_basename($name);
        $namespace = Str::of($name)->beforeLast('\\')->after(app()->getNamespace());

        return (string) $namespace->finish('\\') . $class . $suffix;
    }
}
