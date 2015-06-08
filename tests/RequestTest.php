<?php
use Fol\Http\Request;
use Fol\Http\Globals;
use Fol\Http\Uri;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new Request();
    }

    public function testRequestTarget()
    {
        $this->assertEquals('/', $this->request->getRequestTarget());

        $request = $this->request->withRequestTarget('/new/value');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals('/', $this->request->getRequestTarget());
        $this->assertEquals('/new/value', $request->getRequestTarget());

        $request->setRequestTarget('/other/value');
        $this->assertEquals('/other/value', $request->getRequestTarget());
    }

    public function testMethod()
    {
        $this->assertEquals('GET', $this->request->getMethod());

        $request = $this->request->withMethod('POST');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals('GET', $this->request->getMethod());
        $this->assertEquals('POST', $request->getMethod());

        $request->setMethod('GET');
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testUri()
    {
        $this->assertEquals('/', (string) $this->request->getUri());

        $request = $this->request->withUri(new Uri('https://example.com:10082/foo/bar?baz=bat'));
        
        $this->assertNotSame($this->request, $request);
        $this->assertEquals('/', $this->request->getUri());
        $this->assertEquals('https://example.com:10082/foo/bar?baz=bat', $request->getUri());

        $request->setUri(new Uri('/baz/bat?foo=bar'));
        $this->assertEquals('/baz/bat?foo=bar', (string) $request->getUri());
        $this->assertInstanceOf('Psr\\Http\\Message\\UriInterface', $request->getUri());
    }

    public function testAjax()
    {
        $this->assertFalse($this->request->isAjax());

        $request = $this->request->withHeader('X-Requested-With', 'xmlhttprequest');
        $this->assertTrue($request->isAjax());

        $request->headers->set('X-Requested-With', 'none');
        $this->assertFalse($request->isAjax());

        $request->headers->delete('X-Requested-With');
        $this->assertFalse($request->isAjax());
    }
}
