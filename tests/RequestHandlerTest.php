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

        $this->assertInstanceOf('Fol\\Http\\Request', $request);
        $this->assertInstanceOf('Fol\\Http\\Sessions\\Session', $request->session);

        $this->assertEquals(23, $request->session->getId());
        $this->assertEquals('name', $request->session->getName());

        //Prepare
        $response = new Response();
        $handler->prepare($response);

        $this->assertEquals($response->headers->getDateTime('Date'), new \Datetime());
    }
}
