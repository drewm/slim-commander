# SlimCommander

A very simple structure for running CLI commands as part of your Slim Framework application.

This is not a console tool. It's just a parallel to the HTTP entry point into your application, 
enabling you to do things like create create scripts to be run as cronjobs or set up basic queue listeners.

## Usage 

Taking the structure of Slim-Skeleton as an example, your `public/index.php` does this:

```php
require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
```

You need to create a new PHP script, similar to this, to serve as the entry point for your commands. 
It should be outside the `public` folder. Perhaps `src/cli.php`.

```php
require __DIR__ . '/../vendor/autoload.php';

// Instantiate the app
$settings = require __DIR__ . '/settings.php';
$app = new \DrewM\SlimCommander\App($settings);

// Set up dependencies
require __DIR__ . '/dependencies.php';

// Register commands instead of routes
require __DIR__ . '/commands.php';

// Run app
$app->run($argv);
```

Instead of routes, you define commands in e.g. `src/commands.php`.

```php
$app->command('HelloWorld', 'HelloWorld:greet', [
    'name',
]);
```

Arguments are:

1. Name of the command
2. The callback, defined in the same way as a regular Slim route callback
3. An array of expected argument names

In the above example, the first argument will be passed to the callback as `name`

Your callback gets the container passed to its constructor:

```php
class HelloWorld
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function greet($args)
    {
        echo "Hello " . $args['name'];
    }
}
```

Add it to your container, just as you would normally:

```php
$container['HelloWorld'] = function ($container) {
    return new \App\Commands\HelloWorld($container);
};
```

And then you'd execute it with `php src/cli.php HelloWorld Fred`