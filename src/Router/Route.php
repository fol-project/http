<?php
/**
 * Fol\Http\Router\Route
 *
 * Base class for all routes
 */
namespace Fol\Http\Router;

use Fol\Http\Request;
use Fol\Http\Response;

abstract class Route
{
    /**
     * Constructor
     *
     * @param array $config The available options
     */
    public function __construct(array $config = array())
    {
        foreach (array_keys(get_object_vars($this)) as $key) {
            if (isset($config[$key])) {
                $this->$key = $config[$key];
            }
        }
    }

    /**
     * Execute the route
     *
     * @param Request  $request   The request to send to the controller
     * @param Response $response  The response to send to the controller
     * @param array    $arguments Extra arguments passed to the controller (after $request and $response instances)
     */
    public function execute(Request $request, Response $response, array $arguments = array())
    {
        ob_start();

        array_unshift($arguments, $request, $response);
        $request->route = $this;

        if (!is_array($this->target) || is_object($this->target[0])) {
            $return = call_user_func_array($this->target, $arguments);
        } else {
            list($class, $method) = $this->target;

            $class = new \ReflectionClass($class);

            $controller = $class->hasMethod('__construct') ? $class->newInstanceArgs($arguments) : $class->newInstance();

            $return = $class->getMethod($method)->invokeArgs($controller, $arguments);

            unset($controller);
        }

        if ($return instanceof Response) {
            $response = $return;
            $response->getBody()->write(ob_get_clean());
        } else {
            $response->getBody()->write(ob_get_clean().$return);
        }

        return $response;
    }
}
