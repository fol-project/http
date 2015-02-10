<?php
/**
 * Fol\Http\Middleware
 *
 * Middleware
 */
namespace Fol\Http;

class MiddlewareStack
{
    protected $app;
    protected $baseUrl;
    protected $request;
    protected $response;
    protected $middlewares = [];

    /**
     * Contructor.
     *
     * @param mixed $app
     */
    public function __construct($app = null)
    {
        $this->setBaseUrl(($app instanceof \Fol\App) ? $app->getUrl() : '');
        $this->app = $app;
    }

    /**
     * Magic method to execute this middleware stack as middleware
     * 
     * @param Request  $request
     * @param Response $response
     * 
     * @return Response
     */
    public function __invoke($request, $response)
    {
        return $this->run($request, $response);
    }

    /**
     * Returns the app
     *
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Set the base url used in this stack
     *
     * @param string|Url $url
     */
    public function setBaseUrl($url)
    {
        if (!($url instanceof Url)) {
            $this->baseUrl = new Url($url);
        } else {
            $this->baseUrl = $url;
        }
    }

    /**
     * Returns the base url used in this stack
     *
     * @return Url
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Changes the request of the middleware stack
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Changes the response of the middleware stack
     *
     * @param Request $request
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the request of the middleware stack
     *
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the response of the middleware stack
     *
     * @return null|Request
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Pushes a middleware in the stack.
     *
     * @param callable $middleware
     */
    public function push(callable $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Unshift a middleware in the stack
     *
     * @param callable
     */
    public function unshift(callable $middleware)
    {
        array_unshift($this->middlewares, $middleware);
    }

    /**
     * Removes the last middleware and returns it
     *
     * @return callable|null
     */
    public function pop()
    {
        return array_pop($this->middlewares);
    }

    /**
     * Removes the first middleware and returns it
     *
     * @return callable|null
     */
    public function shift()
    {
        return array_shift($this->middlewares);
    }

    /**
     * Run the next middleware
     *
     */
    public function next()
    {
        if (($middleware = next($this->middlewares))) {
            call_user_func($middleware, $this->request, $this->response, $this);
        }
    }

    /**
     * Run middleware stack
     *
     * @param Request  $request
     * @param Response $response
     * 
     * @return Response
     */
    public function run(Request $request, Response $response)
    {
        $this->setRequest($request);
        $this->setResponse($response);

        if (($middleware = reset($this->middlewares))) {
            call_user_func($middleware, $this->request, $this->response, $this);
        }

        $this->response->prepare($this);
    }
}
