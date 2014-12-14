<?php
/**
 * Fol\Http\Router\Router
 *
 * Class to manage all routes
 */
namespace Fol\Http\Router;

use Fol\Http\ContainerTrait;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;

class Router
{
    use ContainerTrait;

    private $errorController;
    private $routeFactory;

    /**
     * Constructor function. Defines the base url
     *
     * @param RouteFactory $routeFactory
     */
    public function __construct(RouteFactory $routeFactory)
    {
        $this->routeFactory = $routeFactory;
    }

    /**
     * Route factory method
     * Maps the given URL to the given target.
     *
     * @param mixed $name   The route name.
     * @param array $config Array of optional arguments.
     */
    public function map($name, array $config = array())
    {
        if (is_array($name)) {
            foreach ($name as $name => $config) {
                $this->set($name, $this->routeFactory->createRoute($name, $config));
            }

            return;
        }

        $this->set($name, $this->routeFactory->createRoute($name, $config));
    }

    /**
     * Define the router used on errors
     *
     * @param mixed $target The target of this route
     */
    public function setError($target)
    {
        $this->errorController = $this->routeFactory->createErrorRoute($target);
    }

    /**
     * Match given request url and request method and see if a route has been defined for it
     *
     * @param Request $request
     *
     * @return Route|false
     */
    public function match(Request $request)
    {
        foreach ($this->items as $route) {
            if ($route->match($request)) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Reverse route a named route
     *
     * @param string $name   The name of the route to reverse route.
     * @param array  $params Optional array of parameters to use in URL
     *
     * @return string The url to the route
     */
    public function getUrl($name, array $params = array())
    {
        if (!isset($this->items[$name])) {
            throw new \Exception("No route with the name $name has been found.");
        }

        return $this->items[$name]->generate($params);
    }

    /**
     * Handle a request
     *
     * @param Request $request
     * @param array   $arguments The arguments passed to the controller (after $request and $response instances)
     *
     * @throws HttpException If no errorController is defined and an exception is thrown
     *
     * @return Response
     */
    public function handle(Request $request, array $arguments = array())
    {
        try {
            if (($route = $this->match($request))) {
                $response = $route->execute($request, new Response(), $arguments);
            } else {
                throw new HttpException('Not found', 404);
            }

        } catch (HttpException $exception) {
            $level = ob_get_level();

            while ($level > 0) {
                ob_get_clean();
                $level--;
            }

            if ($this->errorController) {
                $this->errorController->set('exception', $exception);
                return $this->errorController->execute($request, new Response('', $exception->getCode() ?: 500), $arguments);
            }

            throw $exception;
        }

        return $response;
    }
}
