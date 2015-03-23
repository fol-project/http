<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Middlewares;

class DigestAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $stack = new Middlewares\Middleware();
        $stack->push(new Middlewares\DigestAuthentication(null, 'Login', 'xxx'));

        $request = new Request('/');
        $response = $stack->run($request);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Digest realm="Login",qop="auth",nonce="xxx",opaque="'.md5('Login').'"', $response->headers->get('WWW-Authenticate'));
    }
}
