<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Middlewares\Middleware;
use Fol\Http\Sessions\Session;

class MiddlewareStackTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $stack = new Middleware();

        $request = new Request('http://domain.com');
        $response = new Response();
        $session = new Session(23, 'name');

        $stack->push($session);

        $result = $stack->run($request, $response);

        $this->assertInstanceOf('Fol\\Http\\Response', $result);
        $this->assertSame($response, $result);
        $this->assertSame($request->attributes->get('SESSION'), $session);

        return [$request, $response];
    }

    /**
     * @depends testOne
     */
    public function testSession(array $arguments)
    {
        list($request, $response) = $arguments;

        $session = $request->attributes->get('SESSION');

        $this->assertInstanceOf('Fol\\Http\\Sessions\\Session', $session);

        $this->assertEquals(23, $session->getId());
        $this->assertEquals('name', $session->getName());

        $this->assertEquals($session->getId(), $response->cookies->get($session->getName())['value']);
    }
}
