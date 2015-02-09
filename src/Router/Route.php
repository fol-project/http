<?php
/**
 * Fol\Http\Router\Route
 *
 * Base class for all routes
 */
namespace Fol\Http\Router;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;

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
     * Run the route as a middleware
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, MiddlewareStack $stack)
    {
        return $this->run($request, $response, $stack);
    }

    /**
     * Execute the route
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function run(Request $request, Response $response, MiddlewareStack $stack)
    {
        $request->attributes->set('route', $this);

        try {
            ob_start();

            if (is_array($this->target) && !is_object($this->target[0])) {
                list($class, $method) = $this->target;

                $class = new \ReflectionClass($class);
                $controller = $class->newInstance($request, $response, $stack);
                $return = $class->getMethod($method)->invoke($controller, $request, $response, $stack);

                unset($controller);
            } elseif (is_callable($this->target)) {
                $return = call_user_func($this->target, $request, $response, $stack);
            } else {
                throw new \Exception("Invalid target for the route {$this->name}");
            }

            if ($return instanceof Response) {
                $response = $return;
                $response->getBody()->write(ob_get_contents());
            } else {
                $response->getBody()->write(ob_get_contents().$return);
            }

            return $response;
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            ob_end_clean();
        }
    }
}
