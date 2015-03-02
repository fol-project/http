<?php
use Fol\Http\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $response = new Response('hello world');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());

        $body = $response->getBody();
        $this->assertSame('hello world', (string) $body);
    }

    public function testRedirect()
    {
        $response = new Response();

        $response->redirect('http://domain.com');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://domain.com', $response->headers->get('Location'));
    }
}
