<?php
chdir(__DIR__);

if ($_SERVER['REQUEST_URI'] === '/ping') {
    echo 'PHP_OK';
    exit(0);
}

const PRODUCTION = true;
const ROOT_DIR = __DIR__;
const SSH_KEYS_DIR = __DIR__ . '/ssh_keys';
const STORAGE_DIR = __DIR__ . '/storage';
const REPOS_DIR = __DIR__ . '/storage/repos';
const SANDBOX_DIR = __DIR__ . '/storage/sandbox';

require_once('app/helpers.php');
require_once('debug.php');
ini_set('date.timezone', 'UTC');

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require 'vendor/autoload.php';
$loader->add('', 'app/');
$loader->addPsr4('App\\', 'app/');

$app = new \Admin\App([
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
    ],
    'foundHandler' => function () {
        return new \Admin\ArgumentsToActionStrategy();
    },
]);

$bladeRenderer = new \eftec\bladeone\BladeOne(
    './app/Http/View',
    STORAGE_DIR . '/cache/compiles'
);


$container = $app->getContainer();
//$container['renderer'] = $renderer;
$container['blade'] = $bladeRenderer;

$view = new \Admin\DoView();
$view->setApp($app);
$container['view'] = $view;

try {
    // BASIC AUTH
    if (env('HTTP_BASIC_AUTH')) {
        $hosts = env('HTTP_BASIC_AUTH_HOSTS', "localhost, 127.0.0.1");
        $hosts = array_map('trim', explode(',', $hosts));

        if (!env('HTTP_BASIC_AUTH_USER') || !env('HTTP_BASIC_AUTH_PASS')) {
            $app->error('Failed to setup auth credentials for basic auth');
        }

        $app->add(new \Slim\Middleware\HttpBasicAuthentication([
            "users" => [
                env('HTTP_BASIC_AUTH_USER') => env('HTTP_BASIC_AUTH_PASS'),
            ],
            "relaxed" => $hosts,
        ]));
    }

    // Define auth resource
    $container = $app->getContainer();
    $container['auth'] = function () {
        return new \User\Auth();
    };



    $app->add(function(\Slim\Http\Request $request, $response, $next) {

        /** @var $this \Slim\Container */
        /** @var $route \Slim\Route */
        $route = $request->getAttribute('route');

        $callable = $route->getCallable();

        if (is_string($callable)) {
            list($class, $action) = explode(':', $callable);
        } elseif (is_array($callable)) {
            list($class, $action) = $callable;
        }

        if (isset($class)) {
            $controller = new $class($this, $request, $response);

            // TODO: looks like it can be implemented via middlewares
            if (method_exists($controller, 'before')) {
                $controller->before();
            }
            // set charged callable for route!
            $route->setCallable([$controller, $action]);
            $request->withAttribute('route', $route);
        }

        $response = $next($request, $response);

        if (isset($class)) {
            // TODO: looks like it can be implemented via middlewares
            if (isset($controller) && method_exists($controller, 'after')) {
                $controller->after();
            }
        }

        return $response;
    });

    // NEW ROUTES HERE!
    $app->get(
        '/projects[/]',
        [\App\Http\Controller\ProjectsController::class, 'index']
    );

    $app->get(
        '/projects/{id}',
        [\App\Http\Controller\ProjectsController::class, 'show']
    );

    $app->get(
        '/packs/{id}',
        [\App\Http\Controller\PacksController::class, 'show']
    );

    // OLD COMMON ROUTE FOR ALL
//    $app->map('/(:module(/)(:controller(/)(:action(/))(:id)))', [$app, 'doRoute'])
//        ->via(\Slim\Http\Request::METHOD_GET, \Slim\Http\Request::METHOD_HEAD, \Slim\Http\Request::METHOD_POST);
    $app->any('/[{module}[/[{controller}[/[{action}[/[{id}]]]]]]]', function ($request, $response, $args) use ($app) {
        $callable = [$app, 'doRoute'];

        return call_user_func($callable, ...$args);
    });
//    $app->any('/(:module(/)(:controller(/)(:action(/))(:id)))', [$app, 'doRoute']);

//    $app->notFound(function () use ($app) {
//        echo $app->request->getResourceUri() . ' not found';
//    });

    $app->run();

} catch (\Exception $e) {
    echo '<pre>';
    echo "ERROR!\n";
    echo 'Exception: ' . $e->getMessage() . " in {$e->getFile()} on {$e->getLine()}" . PHP_EOL;
    echo "\n";
    echo $e->getTraceAsString();
    echo '</pre>';
    echo $app->getContainer()->get('response')->getBody();
    exit;
}