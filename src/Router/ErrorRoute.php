<?php
/**
 * Fol\Http\Router\ErrorRoute
 *
 * Class to manage an error route
 */
namespace Fol\Http\Router;

use Fol\Http\ContainerTrait;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;

class ErrorRoute implements \ArrayAccess
{
    use ContainerTrait;

    public $target;

    /**
     * Constructor
     *
     * @param array $config One available configuration: target
     */
    public function __construct(array $config)
    {
        $this->target = $config['target'];
    }

    /**
     * Execute the route
     *
     * @param HttpException $exception
     * @param Request       $request
     * @param array         $arguments The arguments passed to the controller (after $request and $response instances)
     */
    public function execute(HttpException $exception, Request $request, array $arguments = array())
    {
        ob_start();

        $response = new Response('', $exception->getCode() ?: 500);

        array_unshift($arguments, $request, $response);
        $this->set('exception', $exception);
        $request->route = $this;

        if (!is_array($this->target) || is_object($this->target[0])) {
            $return = call_user_func_array($this->target, $arguments);
        } else {
            list($class, $method) = $this->target;

            $class = new \ReflectionClass($class);

            $controller = $class->hasMethod('__construct') ? $class->newInstanceArgs($arguments) : $class->newInstance();
            $return = $class->getMethod($method)->invokeArgs($controller, $arguments);
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
