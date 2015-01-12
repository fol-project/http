<?php
/**
 * Fol\Http\Router\RegexRoute
 *
 * Class to manage a http route using regular expressions for the path
 */
namespace Fol\Http\Router;

use Fol\Http\Request;

class RegexRoute extends StaticRoute
{
    protected $regex;
    protected $filters;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $config = array())
    {
        parent::__construct($config);

        if (empty($config['regex'])) {
            $this->regex = self::setRegex($this->path, isset($config['filters']) ? $config['filters'] : []);
        }
    }

    /**
     * Generates the regex
     *
     * @param string $path
     * @param array  $filters
     *
     * @return string
     */
    private static function setRegex($path, array $filters)
    {
        return preg_replace_callback('/\{([^\}]*)\}/', function ($matches) use ($filters) {
            $name = $matches[1];
            $filter = isset($filters[$name]) ? $filters[$name] : '[^/]+';

            return "(?P<{$name}>{$filter})";
        }, $path);
    }

    /**
     * Generates and return the regex
     *
     * @param null|string $basePath
     *
     * @return string
     */
    private function getRegex($basePath)
    {
        if (empty($this->regex)) {
            $this->regex = preg_replace_callback('/\{([^\}]*)\}/', function ($matches) {
                $name = $matches[1];
                $filter = isset($this->filters[$name]) ? $this->filters[$name] : '[^/]+';

                return "(?P<{$name}>{$filter})";
            }, $path);
        }

        return '#^'.($basePath === '/' ? '' : $basePath).$this->regex.'$#';
    }

    /**
     * Check the regex of the request
     *
     * @param string      $path     The path
     * @param null|string $basePath The path
     *
     * @return array|false
     */
    public function checkRegex($path, $basePath)
    {
        $regex = $this->getRegex($basePath);

        if (preg_match($regex, $path, $matches)) {
            $params = [];

            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }

            return $params;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function match(Request $request, array $baseUrl)
    {
        $match = (
               self::check($this->ip, $request->getIp())
            && self::check($this->method, $request->getMethod())
            && self::check($this->language, $request->getLanguage())
            && self::check($this->scheme, $request->url->getScheme(), $baseUrl['scheme'])
            && self::check($this->host, $request->url->getHost(), $baseUrl['host'])
            && self::check($this->port, $request->url->getPort(), $baseUrl['port'])
        );

        if ($match && ($matches = $this->checkRegex($request->url->getPath(), $baseUrl['path'])) !== false) {
            $request->attributes->set($matches);

            return true;
        }

        return false;
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
        $path = $this->path;

        foreach ($parameters as $name => $value) {
            if (strpos($path, '{'.$name.'}') !== false) {
                $path = str_replace('{'.$name.'}', rawurlencode($value), $path);
                unset($parameters[$name]);
            }
        }

        $values = $this->getProperties(['scheme', 'host', 'port']);

        return Url::build($values['scheme'], $values['host'], $values['port'], null, null, $path, $parameters);
    }
}
