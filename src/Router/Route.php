<?php
/**
 * Fol\Http\Router\Route
 *
 * Class to manage a http route
 */
namespace Fol\Http\Router;

use Fol\Http\ContainerTrait;
use Fol\Http\Url;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;

class Route implements \ArrayAccess
{
    use ContainerTrait;

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
     * Constructor
     *
     * @param string $name   The route name
     * @param array  $config The available options
     * @param mixed  $target The route target
     */
    public function __construct($name, array $config = array(), $target)
    {
        $this->name = $name;
        $this->target = $target;

        foreach (['ip', 'method', 'scheme', 'host', 'port', 'path', 'language'] as $key) {
            if (isset($config[$key])) {
                $this->$key = $config[$key];
            }
        }
    }

    /**
     * Check two values
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return bool
     */
    public function check($name, $value)
    {
        if (($routeValue = $this->$name) === null) {
            return true;
        }

        if (is_array($routeValue)) {
            return in_array($value, $routeValue, true);
        }

        return ($value === $routeValue);
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
        return (
               $this->check('ip', $request->getIp())
            && $this->check('method', $request->getMethod())
            && $this->check('language', $request->getLanguage())
            && $this->check('scheme', $request->url->getScheme())
            && $this->check('host', $request->url->getHost())
            && $this->check('port', $request->url->getPort())
            && $this->check('path', $request->url->getPath())
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
     * Execute the route and returns the response object
     *
     * @param Request $request   The request to send to controller
     * @param array   $arguments The arguments passed to the controller (after $request and $response instances)
     *
     * @return Response
     */
    public function execute(Request $request, array $arguments = array())
    {
        ob_start();

        $return = '';
        $response = new Response;

        array_unshift($arguments, $request, $response);
        $request->route = $this;

        try {
            if (!is_array($this->target) || is_object($this->target[0])) {
                $return = call_user_func_array($this->target, $arguments);
            } else {
                list($class, $method) = $this->target;

                $class = new \ReflectionClass($class);

                $controller = $class->hasMethod('__construct') ? $class->newInstanceArgs($arguments) : $class->newInstance();

                $return = $class->getMethod($method)->invokeArgs($controller, $arguments);

                unset($controller);
            }
        } catch (\Exception $exception) {
            ob_clean();

            if (!($exception instanceof HttpException)) {
                $exception = new HttpException('Error processing request', 500, $exception);
            }

            throw $exception;
        }

        if ($return instanceof Response) {
            $return->write(ob_get_clean());

            $return->prepare($request);

            return $return;
        }

        $response->write(ob_get_clean().$return);

        $response->prepare($request);

        return $response;
    }
}
