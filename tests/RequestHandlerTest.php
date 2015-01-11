<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Url;
use Fol\Http\RequestHandler;
use Fol\Http\Sessions\Session;

require_once dirname(__DIR__).'/src/autoload.php';

class RequestHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $request = new Request('http://domain.com');
        $handler = new RequestHandler($request);

        $handler->register('session', function ($handler) {
            return new Session($handler, 23, 'name');
        });

        $this->assertInstanceOf('Fol\\Http\\Request', $handler->getRequest());

        return $handler;
    }

    /**
     * @depends testOne
     */
    public function testSession(RequestHandler $handler)
    {
        $request = $handler->getRequest();
        $session = $request->session;

        $this->assertInstanceOf('Fol\\Http\\Sessions\\Session', $session);

        $this->assertEquals(23, $session->getId());
        $this->assertEquals('name', $session->getName());

        //Prepare
        $response = new Response();
        $handler->handle($response);

        $this->assertEquals($session->getId(), $response->cookies->get($session->getName())['value']);
    }

    public function testHandle()
    {
        $request = new Request('http://domain.com', 'HEAD');
        $response = new Response('This is a response');
        
        $handler = new RequestHandler($request);

        $handler->handle($response);

        $this->assertEquals($response->headers->getDateTime('Date'), new \Datetime());
        $this->assertEquals('', (string) $response->getBody());
    }
}
