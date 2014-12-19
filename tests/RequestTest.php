<?php
use Fol\Http\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $request = new Request('http://mydomain.com', 'post', ['Accept' => 'text/plain']);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('text/plain', $request->headers->get('Accept'));
        $this->assertEquals('txt', $request->getFormat());
    }
}
