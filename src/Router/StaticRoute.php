<?php
/**
 * Fol\Http\Router\StaticRoute
 *
 * Class to manage a http static route (the path is a string, not a regex)
 */
namespace Fol\Http\Router;

use Fol\Http\Url;
use Fol\Http\Request;

class StaticRoute extends Route
{
    public $name;
    public $target;

    public $ip;
    public $method;
    public $scheme;
    public $host;
    public $port;
    public $path;
    public $language;

    /**
     * Check whether or not the route match with the request
     *
     * @param Request $request The request to check
     *
     * @return boolean
     */
    public function match(Request $request)
    {
        return (
               self::check($this->ip, $request->getIp())
            && self::check($this->method, $request->getMethod())
            && self::check($this->language, $request->getLanguage())
            && self::check($this->scheme, $request->url->getScheme())
            && self::check($this->host, $request->url->getHost())
            && self::check($this->port, $request->url->getPort())
            && self::check($this->path, $request->url->getPath())
        );
    }

    /**
     * Get the route properties
     *
     * @param array $properties The properties to return
     *
     * @return array
     */
    protected function getProperties(array $properties)
    {
        $values = [];

        foreach ($properties as $name) {
            $values[$name] = is_array($this->$name) ? $this->$name[0] : (string) $this->$name;
        }

        return $values;
    }

    /**
     * Reverse the route
     *
     * @param array $parameters Optional array of parameters to use in URL
     *
     * @return string The url to the route
     */
    public function generate(array $parameters = array())
    {
        $values = $this->getProperties(['scheme', 'host', 'port', 'path']);

        return Url::build($values['scheme'], $values['host'], $values['port'], null, null, $values['path'], $parameters);
    }

    /**
     * Check two values
     *
     * @param mixed $routeValue
     * @param mixed $requestValue
     *
     * @return boolean
     */
    protected static function check($routeValue, $requestValue)
    {
        if ($routeValue === null || $requestValue === null) {
            return true;
        }

        if (is_array($routeValue)) {
            return in_array($requestValue, $routeValue, true);
        }

        return ($requestValue === $routeValue);
    }
}
