<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Handler;
use Fol\Http\Sessions\Session;

require_once dirname(__DIR__).'/src/autoload.php';

class HandlerTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $request = new Request('http://domain.com');
        $handler = new Handler($request);

        $handler->register('session', function ($handler) {
            return new Session($handler, 23, 'name');
        });

        $this->assertInstanceOf('Fol\\Http\\Request', $handler->getRequest());

        return $handler;
    }

    /**
     * @depends testOne
     */
    public function testSession(Handler $handler)
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
        $request = new Request('http://domain.com/index.json', 'HEAD');
        $response = new Response('This is a response');

        $handler = new Handler($request);

        $handler->handle($response);

        $this->assertEquals($response->headers->get('Content-Type'), 'application/json; charset=UTF-8');

        $this->assertEquals($response->headers->get('Date'), (new \Datetime('now', new \DateTimeZone('GMT')))->format('D, d M Y H:i:s').' GMT');
        $this->assertEquals('', (string) $response->getBody());
    }
}
