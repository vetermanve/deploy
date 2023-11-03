<?php

namespace Admin;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
class ArgumentsToActionStrategy implements InvocationStrategyInterface
{

    /**
     * Invoke a route callable with request, response and all route parameters
     * as individual arguments.
     *
     * @param array|callable         $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $routeArguments
     *
     * @return ResponseInterface
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ) {
        // NOTE: for backward compability with old controllers and routes
        if (array_key_exists('module', $routeArguments)) {
            foreach ($routeArguments as $k => $v) {
                $request = $request->withAttribute($k, $v);
            }

            return call_user_func($callable, $request, $response, $routeArguments);
        }

        return call_user_func_array($callable, $routeArguments);
    }
}
