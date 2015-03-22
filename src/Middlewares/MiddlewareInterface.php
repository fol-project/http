<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;

/**
 * Interface used by all class-based middlewares.
 */
interface MiddlewareInterface
{
    /**
     * Magic method to execute this middleware as callable.
     *
     * @param Request         $request
     * @param Response        $response
     * @param Middlewarestack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, Middleware $stack);
}
