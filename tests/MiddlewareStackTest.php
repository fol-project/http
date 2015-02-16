<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\Sessions\Session;

class MiddlewareStackTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $stack = new MiddlewareStack();

        $stack->push(new Session(23, 'name'));

        $stack->run(new Request('http://domain.com'), new Response);

        $this->assertInstanceOf('Fol\\Http\\Request', $stack->getRequest());
        $this->assertInstanceOf('Fol\\Http\\Response', $stack->getResponse());

        return $stack;
    }

    /**
     * @depends testOne
     */
    public function testSession(MiddlewareStack $stack)
    {
        $session = $stack->getRequest()->attributes->get('session');

        $this->assertInstanceOf('Fol\\Http\\Sessions\\Session', $session);

        $this->assertEquals(23, $session->getId());
        $this->assertEquals('name', $session->getName());

        $response = $stack->getResponse();
        $this->assertEquals($session->getId(), $response->cookies->get($session->getName())['value']);
    }

    public function testPrepare()
    {
        $stack = new MiddlewareStack();
        $stack->setBaseUrl('http://domain.com/my-new-site');

        $stack->push(function ($request, $response, $stack) {
            $response->cookies->set('My-cookie', 'value');
            $stack->next();
        });

        $stack->run(new Request('http://domain.com/index.json', 'HEAD'), new Response('This is a response'));

        $response = $stack->getResponse();

        $this->assertEquals($response->headers->get('Content-Type'), 'application/json; charset=UTF-8');

        $this->assertEquals($response->headers->get('Date'), (new \Datetime('now', new \DateTimeZone('GMT')))->format('D, d M Y H:i:s').' GMT');
        $this->assertEquals('', (string) $response->getBody());

        $this->assertEquals('/my-new-site', $response->cookies->get('My-cookie')['path']);
        $this->assertEquals('domain.com', $response->cookies->get('My-cookie')['domain']);
    }
}
