<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\Middlewares;

class BasicAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $stack = new MiddlewareStack();
        $stack->push(new Middlewares\BasicAuthentication(null, 'Login'));

        $request = new Request('/');
        $response = $stack->run($request);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Basic realm="Login"', $response->headers->get('WWW-Authenticate'));
    }
}
