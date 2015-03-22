<?php
namespace Fol\Http\Router;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Middlewares\Middleware;

/**
 * Base class for all routes
 */
abstract class Route
{
    public $name;
    public $target;

    /**
     * Constructor.
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
     * Run the route as a middleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, Middleware $stack)
    {
        return $this->run($request, $response, $stack);
    }

    /**
     * Execute the route.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function run(Request $request, Response $response, Middleware $stack)
    {
        $request->attributes->set('ROUTE', $this);
        $app = $stack->getRoot()->getApp();

        try {
            ob_start();

            if (is_array($this->target) && !is_object($this->target[0])) {
                list($class, $method) = $this->target;

                $class = new \ReflectionClass($class);
                $controller = $class->hasMethod('__construct') ? $class->newInstance($request, $response, $app) : $class->newInstance();
                $return = $class->getMethod($method)->invoke($controller, $request, $response, $app);

                unset($controller);
            } elseif (is_callable($this->target)) {
                $return = call_user_func($this->target, $request, $response, $app);
            } else {
                throw new \Exception("Invalid target for the route {$this->name}");
            }

            $response->getBody()->write(ob_get_contents().$return);
        } catch (\Exception $exception) {
            throw $exception;
        } finally {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
    }
}
