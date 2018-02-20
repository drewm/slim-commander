<?php

namespace DrewM\SlimCommander;

use Psr\Container\ContainerInterface;
use Slim\Container;
use InvalidArgumentException;
use Exception;

class App
{
    private $commands = [];
    private $container;

    /**
     * Create a new Commader app.
     *
     * @param ContainerInterface|array $container Either a ContainerInterface or an associative array of app settings
     *
     * @throws InvalidArgumentException when no container is provided that implements ContainerInterface
     */
    public function __construct($container = [])
    {
        if (is_array($container)) {
            $container = new Container($container);
        }
        if (!$container instanceof ContainerInterface) {
            throw new InvalidArgumentException('Expected a ContainerInterface');
        }
        $this->container = $container;
    }

    /**
     * Enable access to the DI container by consumers of $app
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     *
     * Add a CLI command
     *
     * @param string          $name     Command name
     * @param string|callable $callable Class and method to invoke
     * @param array|null      $args     Arguments expected
     */
    public function command($name, $callable, $args)
    {
        $this->commands[$name] = [
            'callable' => $callable,
            'args'     => (!empty($args) ? $args : []),
        ];
    }

    /**
     *
     * Run! Process and execute the matching command.
     *
     * @param array $args Should be PHP's $argv
     *
     * @throws Exception when no matching command is found.
     *
     * @return mixed
     */
    public function run($args)
    {
        list($command, $envArgs) = $this->prepareCommand($args);

        if (array_key_exists($command, $this->commands)) {
            $definition  = $this->commands[$command];
            $commandArgs = $this->prepareArgs($definition['args'], $envArgs);
            $callable    = $this->resolveCallable($definition['callable']);
            return $this->dispatch($callable, $commandArgs);
        }

        throw new Exception(sprintf('No matching command for ‘%s’', $command));

    }

    /**
     *
     * Format the arguments for passing through to the command.
     *
     * @param array $defArgs Arguments from the command definition
     * @param array $envArgs Arguments from the environment ($argv)
     *
     * @return array
     */
    private function prepareArgs(array $defArgs, array $envArgs)
    {
        $commandArgs = [];

        if (count($defArgs)) {
            foreach ($defArgs as $defArg) {
                $commandArgs[$defArg] = array_shift($envArgs);
            }
        }

        if (count($envArgs)) {
            foreach ($envArgs as $envArg) {
                $commandArgs['arg_' . count($commandArgs)] = $envArg;
            }
        }

        return $commandArgs;
    }

    /**
     * Find the command and arguments from the raw $argv input
     *
     * @param array $args Arguments form $argv
     *
     * @return array
     */
    private function prepareCommand(array $args)
    {
        // bump the file name
        array_shift($args);

        // Command
        $command = array_shift($args);

        return [$command, $args];
    }

    /**
     * Use Slim infrastructure to turn faux-callable like "Class:method" into actual callable.
     * Creates the callable and passes in the container.
     *
     * @param  callable|string $callable The callback routine
     *
     * @return callable
     */
    private function resolveCallable($callable)
    {
        $resolver = $this->container->get('callableResolver');
        return $resolver->resolve($callable);
    }

    /**
     * Execute the command.
     *
     * @param callable $callable
     * @param array    $args
     *
     * @return mixed
     */
    private function dispatch(callable $callable, array $args)
    {
        return $callable($args);
    }

}