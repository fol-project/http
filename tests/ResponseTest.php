<?php
use Fol\Http\Response;

require_once dirname(__DIR__).'/src/autoload.php';

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $response = new Response('hello world');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());

        $response->setFormat('json');
        $this->assertSame('application/json; charset=UTF-8', $response->headers->get('Content-Type'));

        $body = $response->getBody();
        $this->assertSame('hello world', (string) $body);

        $response->setLanguage('gl');
        $this->assertSame('gl', $response->headers->get('Content-Language'));
    }

    public function testBasicAuthentication()
    {
        $response = new Response();

        $response->setBasicAuthentication('hello');

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Basic realm="hello"', $response->headers->get('WWW-Authenticate'));
    }

    public function testDigestAuthentication()
    {
        $response = new Response();

        $response->setDigestAuthentication('hello', 'world');

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Digest realm="hello",qop="auth",nonce="world",opaque="'.md5('hello').'"', $response->headers->get('WWW-Authenticate'));
    }

    public function testRedirect()
    {
        $response = new Response();

        $response->redirect('http://domain.com');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://domain.com', $response->headers->get('Location'));
    }
}
