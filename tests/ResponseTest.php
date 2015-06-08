<?php
use Fol\Http\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->response = new Response();
    }

    public function testStatus()
    {
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertSame('OK', $this->response->getReasonPhrase());

        $response = $this->response->withStatus(404);
        $this->assertNotSame($this->response, $response);
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals(404, $response->getStatusCode());

        $response->setStatus(500, 'foo');
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('foo', $response->getReasonPhrase());
    }

    public function testRedirect()
    {
        $response = clone $this->response;

        $response->redirect('http://domain.com');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://domain.com', $response->getHeaderLine('Location'));
    }
}
