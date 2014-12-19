<?php
/**
 * Fol\Http\Router\Route
 *
 * Base class for all routes
 */
namespace Fol\Http\Router;

use Fol\Http\ContainerTrait;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;

abstract class Route implements \ArrayAccess
{
    use ContainerTrait;

    /**
     * Execute the route
     *
     * @param Request       $request   The request to send to the controller
     * @param Response      $response  The response to send to the controller
     * @param array         $arguments Extra arguments passed to the controller (after $request and $response instances)
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

        $request->events->emit('prepareResponse', [$request, $response, $this]);
        $response->events->emit('prepare', [$request, $response, $this]);

        return $response;
    }
}
