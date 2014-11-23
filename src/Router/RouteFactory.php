<?php
/**
 * Fol\Http\Router\RouteFactory
 *
 * Class to generate route classes
 */
namespace Fol\Http\Router;

class RouteFactory
{
    private $namespace;
    private $baseUrl;

    /**
     * Constructor
     *
     * @param string $namespace The namespace where the controllers are located
     * @param string $baseUrl   The url base to prepend to the routes
     */
    public function __construct($namespace, $baseUrl)
    {
        $this->namespace = $namespace;
        $components = parse_url($baseUrl);

        $this->baseUrl = [
            'scheme' => $components['scheme'],
            'host' => $components['host'],
            'port' => isset($components['port']) ? $components['port'] : null,
            'path' => isset($components['path']) ? $components['path'] : ''
        ];
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

        $class = "{$this->namespace}\\{$class}";

        return [$class, $method];
    }

    /**
     * Creates a new route instance
     *
     * @param string $name   Route name
     * @param array  $config Route configuration (path, target, etc)
     *
     * @return Route
     */
    public function createRoute($name, array $config)
    {
        $target = $this->getTarget($config['target']);

        $config['path'] = $this->baseUrl['path'].$config['path'];

        if (isset($config['regex'])) {
            $config['regex'] = $this->baseUrl['path'].$config['regex'];
        }

        if (!isset($config['scheme'])) {
            $config['scheme'] = $this->baseUrl['scheme'];
        }

        if (!isset($config['host'])) {
            $config['host'] = $this->baseUrl['host'];
        }

        if (!isset($config['port'])) {
            $config['port'] = $this->baseUrl['port'];
        }

        if (isset($config['path'][1])) {
            $config['path'] = rtrim($config['path'], '/');
        }

        if (isset($config['regex']) || strpos($config['path'], '{') !== false) {
            return new RegexRoute($name, $config, $target);
        }

        return new Route($name, $config, $target);
    }

    /**
     * Creates a new error route instance
     *
     * @param string $target The error target (ControllerClass::method)
     *
     * @return ErrorRoute
     */
    public function createErrorRoute($target)
    {
        return new ErrorRoute(['target' => $this->getTarget($target)]);
    }
}
