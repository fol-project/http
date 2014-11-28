<?php
/**
 * Fol\Http\Router\RegexRoute
 *
 * Class to manage a http route using regular expressions for the path
 */
namespace Fol\Http\Router;

use Fol\Http\Request;
use Fol\Http\Url;

class RegexRoute extends Route
{
    protected $regex;

    /**
     * {@inheritDoc}
     */
    public function __construct ($name, array $config = array(), $target)
    {
        parent::__construct($name, $config, $target);

        if (empty($config['regex'])) {
            $this->regex = self::setRegex($this->path, isset($config['filters']) ? $config['filters'] : []);
        } else {
            $this->regex = $config['regex'];
        }

        $this->regex = "#^{$this->regex}$#";
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
     * Check the regex of the request
     *
     * @param string $path The path
     *
     * @return array|false
     */
    public function checkRegex($path)
    {
        if (preg_match($this->regex, $path, $matches)) {
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
     * Check if the route match with the request
     *
     * @param Request $request The request to check
     *
     * @return bool
     */
    public function match(Request $request)
    {
        $match = (
               $this->check('ip', $request->getIp())
            && $this->check('method', $request->getMethod())
            && $this->check('language', $request->getLanguage())
            && $this->check('scheme', $request->url->getScheme())
            && $this->check('host', $request->url->getHost())
            && $this->check('port', $request->url->getPort())
        );

        if (!$match || ($matches = $this->checkRegex($request->url->getPath())) === false) {
            return false;
        }

        $this->set($matches);

        return true;
    }

    /**
     * Reverse the route
     *
     * @param array $parameters Optional array of parameters to use in URL
     *
     * @return string The url to the route
     */
    public function generate (array $parameters = array())
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
