<?php
use Fol\Http\Request;
use Fol\Http\Globals;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $request = new Request('http://mydomain.com?hello=world', 'post', ['Accept' => 'text/plain']);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('text/plain', $request->headers->get('Accept'));
        $this->assertEquals('world', $request->query->get('hello'));
    }

    public function testAjax()
    {
        $request = new Request('', 'get', ['X-Requested-With' => 'xmlhttprequest']);

        $this->assertTrue($request->isAjax());

        $request->headers->set('X-Requested-With', 'none');
        $this->assertFalse($request->isAjax());

        $request->headers->delete('X-Requested-With');
        $this->assertFalse($request->isAjax());
    }

    public function testCreateFromGlobal()
    {
        $g = include __DIR__.'/files/global-request.php';
        $global = new Globals($g['_SERVER'], $g['_GET'], $g['_POST'], $g['_FILES'], $g['_COOKIE'], $g['input']);

        $request = Request::createFromGlobals($global);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('http://localhost/test.php', $request->url->getUrl());
        $this->assertEquals(80, $request->url->getPort());
        $this->assertEquals('', (string) $request->getBody());
    }
}
