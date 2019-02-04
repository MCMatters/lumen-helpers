<?php

declare(strict_types = 1);

namespace McMatters\LumenHelpers;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class RouteHelper
 *
 * @package McMatters\LumenHelpers
 */
class RouteHelper
{
    /**
     * @param array $filters
     * @param string|null $closureAs
     *
     * @return array
     */
    public static function getRoutes(
        array $filters = [],
        string $closureAs = null
    ): array {
        $routes = [];

        $filterMethod = Arr::get($filters, 'method');
        $filterName = Arr::get($filters, 'name');
        $filterPath = Arr::get($filters, 'path');

        foreach (Container::getInstance()->router->getRoutes() as $route) {
            if ((null !== $filterMethod && Arr::get($route, 'method') !== $filterMethod) ||
                (null !== $filterName && !Str::contains(Arr::get($route, 'action.as', ''), $filterName)) ||
                (null !== $filterPath && !Str::contains(Arr::get($route, 'uri', ''), $filterPath))
            ) {
                continue;
            }

            $routes[] = self::getRouteInformation($route, $closureAs);
        }

        return $routes;
    }

    /**
     * @param array $route
     * @param string|null $closureAs
     *
     * @return array
     */
    protected static function getRouteInformation(
        array $route,
        string $closureAs = null
    ): array {
        return [
            'method'     => Arr::get($route, 'method'),
            'uri'        => Arr::get($route, 'uri'),
            'name'       => Arr::get($route, 'action.as'),
            'action'     => self::getRouteAction($route, $closureAs),
            'middleware' => self::getRouteMiddleware($route, $closureAs),
        ];
    }

    /**
     * @param array $route
     * @param string|null $closureAs
     *
     * @return string
     */
    protected static function getRouteAction(
        array $route,
        string $closureAs = null
    ): string {
        $action = Arr::get($route, 'action.uses');

        return $action instanceof Closure ? $closureAs : $action;
    }

    /**
     * @param array $route
     * @param string|null $closureAs
     *
     * @return array
     */
    protected static function getRouteMiddleware(
        array $route,
        string $closureAs = null
    ): array {
        $routeMiddleware = Arr::get($route, 'action.middleware', []);

        if ($routeMiddleware instanceof Closure) {
            return [$closureAs];
        }

        $middleware = [];

        foreach ((array) $routeMiddleware as $item) {
            $middleware[] = $routeMiddleware instanceof Closure
                ? $closureAs
                : $item;
        }

        return $middleware;
    }
}
