<?php
chdir(__DIR__);

if ($_SERVER['REQUEST_URI'] === '/ping') {
    echo 'PHP_OK';
    exit(0);
}

define('PRODUCTION', true);

require_once('debug.php');
ini_set('date.timezone', 'UTC');

$loader = require 'vendor/autoload.php';
$loader->add('', 'app/');

$app = new \Admin\App(array(
    'view' => (new \Admin\DoView()),
));

$app->view()->setApp($app);

//$app->add(new \Slim\Middleware\HttpBasicAuthentication([
//    "users" => [
//        "root" => "root",
//    ] 
//]));

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