<?php
chdir(__DIR__);

if ($_SERVER['REQUEST_URI'] === '/ping') {
    echo 'PHP_OK';
    exit(0);
}

define('PRODUCTION', true);
define('ROOT_DIR', __DIR__);
define('STORAGE_DIR', __DIR__ . '/storage');
define('REPOS_DIR', __DIR__ . '/storage/repos');

require_once('app/helpers.php');
require_once('debug.php');
ini_set('date.timezone', 'UTC');

$loader = require 'vendor/autoload.php';
$loader->add('', 'app/');

$app = new \Admin\App(array(
    'view' => (new \Admin\DoView()),
));

$app->view()->setApp($app);

try {
    // BASIC AUTH
    if (env('HTTP_BACIS_AUTH')) {
        $hosts = env('HTTP_BACIS_AUTH_HOSTS', "localhost, 127.0.0.1");
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
    $app->container->singleton('auth', function () {
        return new \User\Auth();
    });

    $app->map('/(:module(/)(:controller(/)(:action(/))(:id)))', [$app, 'doRoute'])
        ->via(\Slim\Http\Request::METHOD_GET, \Slim\Http\Request::METHOD_HEAD, \Slim\Http\Request::METHOD_POST);

    $app->notFound(function () use ($app) {
        echo $app->request->getResourceUri() . ' not found';
    });

    $app->run();

} catch (\Exception $e) {
    echo $app->response()->getBody();
    exit;
}