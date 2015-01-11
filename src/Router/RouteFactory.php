<?php
/**
 * Fol\Http\Router\RouteFactory
 *
 * Class to generate route classes
 */
namespace Fol\Http\Router;

use Fol\Http\RequestHandler;
use Fol\Http\Url;

class RouteFactory
{
    private $namespace;

    /**
     * Constructor
     *
     * @param string $namespace The namespace where the controllers are located
     */
    public function __construct($namespace = '')
    {
        $this->namespace = $namespace;
    }

    /**
     * Generates the target of the route
     *
     * @param string $target (For example: ControllerClass::method)
     *
     * @return array
     */
    private function getTarget($target)
    {
        if ($target instanceof \Closure) {
            return $target;
        }

        if (is_array($target)) {
            return $target;
        }

        if (strpos($target, '::') === false) {
            $class = $target;
            $method = '__invoke';
        } else {
            list($class, $method) = explode('::', $target, 2);
        }

        if ($this->namespace) {
            $class = "{$this->namespace}\\{$class}";
        }

        return [$class, $method];
    }

    /**
     * Normalices the configuration of a route
     *
     * @param string $name   Route name
     * @param array  $config Route configuration (path, target, etc)
     * @param Url $baseUrl The baseUrl of the route
     *
     * @return array
     */
    private function getConfig($name, array $config, Url $baseUrl)
    {
        $config['name'] = $name;
        $config['target'] = $this->getTarget($config['target']);

        $basePath = $baseUrl->getPath(false);

        if ($basePath !== '/') {
            $config['path'] = $basePath.$config['path'];
        }

        $config['path'] = rtrim($config['path'], '/') ?: '/';

        if (isset($config['regex'])) {
            $config['regex'] = $basePath.$config['regex'];
        }

        if (!isset($config['scheme']) && ($scheme = $baseUrl->getScheme())) {
            $config['scheme'] = $scheme;
        }

        if (!isset($config['host']) && ($host = $baseUrl->getHost())) {
            $config['host'] = $host;
        }

        if (!isset($config['port']) && ($port = $baseUrl->getPort())) {
            $config['port'] = $port;
        }

        return $config;
    }

    /**
     * Creates a new route instance
     *
     * @param string $name   Route name
     * @param array  $config Route configuration (path, target, etc)
     * @param Url $baseUrl The baseUrl of the route
     *
     * @return Route
     */
    public function createRoute($name, array $config, Url $baseUrl)
    {
        $config = $this->getConfig($name, $config, $baseUrl);

        if (isset($config['regex']) || strpos($config['path'], '{') !== false) {
            return new RegexRoute($config);
        }

        return new StaticRoute($config);
    }

    /**
     * Creates a new error route instance
     *
     * @param mixed $target The error target
     *
     * @return ErrorRoute
     */
    public function createErrorRoute($target)
    {
        return new ErrorRoute(['target' => $this->getTarget($target)]);
    }
}
