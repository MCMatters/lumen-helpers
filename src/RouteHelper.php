<?php

declare(strict_types=1);

namespace McMatters\LumenHelpers;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

use const null;

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

        $filterMethod = $filters['method'] ?? null;
        $filterName = $filters['name'] ?? null;
        $filterPath = $filters['path'] ?? null;

        foreach (Container::getInstance()->router->getRoutes() as $route) {
            if (
                (null !== $filterMethod && ($route['method'] ?? null) !== $filterMethod) ||
                (null !== $filterName && !Str::contains($route['action']['as'] ?? '', $filterName)) ||
                (null !== $filterPath && !Str::contains($route['uri'] ?? '', $filterPath))
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
            'method' => $route['method'] ?? '',
            'uri' => $route['uri'] ?? '',
            'name' => $route['action']['as'] ?? '',
            'action' => self::getRouteAction($route, $closureAs),
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
        $action = $route['action']['uses'] ?? '';

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
        $routeMiddleware = $route['action']['middleware'] ?? [];

        if ($routeMiddleware instanceof Closure) {
            return [$closureAs];
        }

        $middleware = [];

        foreach ((array) $routeMiddleware as $item) {
            $middleware[] = $item instanceof Closure
                ? $closureAs
                : $item;
        }

        return $middleware;
    }
}
