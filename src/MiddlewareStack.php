<?php
/**
 * Fol\Http\Middleware.
 *
 * Middleware
 */

namespace Fol\Http;

class MiddlewareStack implements MiddlewareInterface
{
    protected $app;
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
        $this->app = $app;
    }

    /**
     * Magic method to execute this middleware stack as middleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param Middlewarestack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, MiddlewareStack $stack)
    {
        $this->run($request, $response);

        $stack->next();
    }

    /**
     * Returns the app.
     *
     * @return mixed
     */
    public function getApp()
    {
        return $this->app;
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
     * Unshift a middleware in the stack.
     *
     * @param callable
     */
    public function unshift(callable $middleware)
    {
        array_unshift($this->middlewares, $middleware);
    }

    /**
     * Removes the last middleware and returns it.
     *
     * @return callable|null
     */
    public function pop()
    {
        return array_pop($this->middlewares);
    }

    /**
     * Removes the first middleware and returns it.
     *
     * @return callable|null
     */
    public function shift()
    {
        return array_shift($this->middlewares);
    }

    /**
     * Run the next middleware.
     */
    public function next()
    {
        if (($middleware = next($this->middlewares))) {
            call_user_func($middleware, $this->request, $this->response, $this);
        }
    }

    /**
     * Run middleware stack.
     *
     * @param Request       $request
     * @param null|Response $response
     *
     * @return Response
     */
    public function run(Request $request, Response $response = null)
    {
        $this->request = $request;
        $this->response = $response ?: new Response();

        if (($middleware = reset($this->middlewares))) {
            call_user_func($middleware, $this->request, $this->response, $this);
        }

        return $this->response;
    }
}
