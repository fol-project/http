<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\MiddlewareInterface;

/**
 * Abstract class used for authentication middlewares.
 */
abstract class Authentication implements MiddlewareInterface
{
    /**
     * Run the middleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, MiddlewareStack $stack)
    {
        if ($this->login($request)) {
            $this->onSuccess($request, $response, $stack);
        } else {
            $this->onError($request, $response, $stack);
        }
    }

    /**
     * Function executed on success.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     */
    protected function onSuccess(Request $request, Response $response, MiddlewareStack $stack)
    {
        $stack->next();
    }

    /**
     * Function executed on error.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     */
    protected function onError(Request $request, Response $response, MiddlewareStack $stack)
    {
        $response->setStatus(401);
        $response->getBody()->write('You must login before enter');
    }

    /**
     * Login function.
     *
     * @param Request $request
     *
     * @return boolean
     */
    abstract protected function login(Request $request);
}
