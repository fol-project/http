<?php
/**
 * Fol\Http\Router\Router
 *
 * Class to manage all routes
 */
namespace Fol\Http\Router;

use Fol\Http\ContainerTrait;
use Fol\Http\Request;
use Fol\Http\RequestHandler;
use Fol\Http\Response;
use Fol\Http\HttpException;

class Router
{
    use ContainerTrait;

    private $errorController;
    private $routeFactory;
    private $handler;

    /**
     * Constructor function. Defines the base url
     *
     * @param RequestHandler    $handler
     * @param null|RouteFactory $routeFactory
     */
    public function __construct(RequestHandler $handler, RouteFactory $routeFactory = null)
    {
        $this->routeFactory = $routeFactory ?: new RouteFactory();
        $this->handler = $handler;
    }

    /**
     * Route factory method
     * Maps the given URL to the given target.
     *
     * @param array|string $name   The route name or array with routes.
     * @param array        $config Array of optional arguments.
     */
    public function map($name, array $config = array())
    {
        if (is_array($name)) {
            foreach ($name as $name => $config) {
                $this->set($name, $this->routeFactory->createRoute($name, $config, $this->handler->getBaseUrl()));
            }

            return;
        }

        $this->set($name, $this->routeFactory->createRoute($name, $config, $this->handler->getBaseUrl()));
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
     * Run the router and send the response
     */
    public function run(array $arguments = array())
    {
        $response = $this->handle($this->handler->getRequest(), $arguments);
        $this->handler->handle($response);

        $response->send();
    }

    /**
     * Handle a specific request
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
            if ($this->errorController) {
                $request->attributes->set('error', $exception);

                return $this->errorController->execute($request, new Response('', $exception->getCode() ?: 500), $arguments);
            }

            throw $exception;
        }

        return $response;
    }
}
