<?php
namespace Fol\Http\Middlewares;

/**
 * Base of a middleware that can contains other middlewares
 */
class Middleware implements MiddlewareInterface
{
    protected $parent;
    protected $request;
    protected $response;
    protected $middlewares = [];

    /**
     * Magic method to execute this middleware stack as a submiddleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param Middlewarestack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, MiddlewareStack $stack)
    {
        $this->parent = $stack;
        $this->run($request, $response);
    }

    /**
     * Returns the root middleware.
     *
     * @return Middleware
     */
    public function getRoot()
    {
        return ($this->parent !== null) ? $this->parent->getRoot() : $this;
    }

    /**
     * Set the middleware app.
     *
     * @param mixed $app
     */
    public function setApp($app)
    {
        $this->app = $app;
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
        } elseif ($this->parent !== null) {
            $this->parent->next();
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
