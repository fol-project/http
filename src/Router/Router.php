<?php
namespace Fol\Http\Router;

use Fol\Bag;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\BodyStream;
use Fol\Http\Url;
use Fol\Http\HttpException;
use Fol\Http\Middlewares\Middleware;
use Fol\Http\Middlewares\MiddlewareInterface;

/**
 * Manage all routes
 */
class Router extends Bag implements MiddlewareInterface
{
    private $baseUrl;
    private $errorRoute;
    private $routeFactory;

    /**
     * Constructor function. Defines the base url.
     *
     * @param null|RouteFactory $routeFactory
     */
    public function __construct(RouteFactory $routeFactory = null)
    {
        $this->routeFactory = $routeFactory ?: new RouteFactory();
    }

    /**
     * Run the router as a middleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     */
    public function __invoke(Request $request, Response $response, Middleware $stack)
    {
        $this->run($request, $response, $stack);
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
                $this->set($name, $this->routeFactory->createRoute($name, $config));
            }

            return;
        }

        $this->set($name, $this->routeFactory->createRoute($name, $config));
    }

    /**
     * Define the router used on errors.
     *
     * @param mixed $target The target of this route
     */
    public function setError($target)
    {
        $this->errorRoute = $this->routeFactory->createErrorRoute($target);
    }

    /**
     * Match given request url and request method and see if a route has been defined for it.
     *
     * @param Request $request
     *
     * @return Route|false
     */
    public function match(Request $request)
    {
        foreach ($this->items as $route) {
            if ($route->match($request, $this->baseUrl)) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Reverse route a named route.
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

        return $this->items[$name]->getUrl($this->baseUrl, $params);
    }

    /**
     * Run the route.
     *
     * @param Request    $request
     * @param Response   $response
     * @param Middleware $stack
     */
    public function run(Request $request, Response $response, Middleware $stack)
    {
        $previousBaseUrl = $this->baseUrl;
        $baseUrl = $request->attributes->get('BASE_URL') ?: new Url('');
        $this->baseUrl = $baseUrl->toArray();

        try {
            if (!($route = $this->match($request))) {
                throw new HttpException('Not found', 404);
            }

            call_user_func($route, $request, $response, $stack);
        } catch (HttpException $exception) {
            if (!$this->errorRoute) {
                throw $exception;
            }

            $request->attributes->set('ERROR', $exception);
            $response->setStatus($exception->getCode() ?: 500);
            $response->setBody(new BodyStream());

            call_user_func($this->errorRoute, $request, $response, $stack);
        }

        $this->baseUrl = $previousBaseUrl;
        $stack->next();
    }
}
