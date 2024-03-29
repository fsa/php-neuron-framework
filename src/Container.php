<?php

namespace FSA\Neuron;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    protected $instances = [];

    public function __construct(protected array $singletons, protected array $dependencies, protected array $parameters)
    {
    }

    public function addSingletons(array $singletons): static
    {
        $this->singletons = array_merge($this->singletons, $singletons);
        return $this;
    }

    public function addDependencies(array $dependencies): static
    {
        $this->dependencies = array_merge($this->dependencies, $dependencies);
        return $this;
    }

    public function addParameters(array $parameters): static
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }

    public function get(string $id)
    {
        if (isset($this->singletons[$id])) {
            if (!isset($this->instances[$id])) {
                $this->instances[$id] = $this->singletons[$id]();
            }
            return $this->instances[$id];
        }
        if (isset($this->dependencies[$id])) {
            return $this->dependencies[$id]();
        }
        if (class_exists($id)) {
            return $this->prepareObject($id);
        }
        return $this->parameters[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->singletons[$id]) or isset($this->dependencies[$id]) or class_exists($id) or isset($this->parameters[$id]);
    }

    private function prepareObject(string $class): object
    {
        $classReflector = new \ReflectionClass($class);
        $constructReflector = $classReflector->getConstructor();
        if (empty($constructReflector)) {
            return new $class;
        }
        $constructArguments = $constructReflector->getParameters();
        if (empty($constructArguments)) {
            return new $class;
        }
        $args = [];
        foreach ($constructArguments as $argument) {
            $argumentType = $argument->getType()->getName();
            $name = $argument->getName();
            switch ($argumentType) {
                case null:
                case 'string':
                case 'int':
                case 'array':
                    if (isset($this->parameters[$class][$name])) {
                        $args[$name] = $this->parameters[$class][$name];
                    } else if (isset($this->parameters[$name])) {
                        $args[$name] = $this->parameters[$name];
                    } else {
                        if ($argument->isDefaultValueAvailable()) {
                            $args[$name] = $argument->getDefaultValue();
                        } else {
                            throw new InvalidArgumentException("'$class' required '$name' constructor argument.");
                        }
                    }
                    break;
                default:
                    $args[$name] = $this->get($argumentType);
            }
        }
        return new $class(...$args);
    }
}
