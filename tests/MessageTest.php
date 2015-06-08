<?php
use Fol\Http\Request;
use Fol\Http\BodyStream;

class MessageTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new Request();
    }

    public function testProtocolVersion()
    {
        $this->assertEquals('1.1', $this->request->getProtocolVersion());

        $request = $this->request->withProtocolVersion('1.1');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals('1.1', $request->getProtocolVersion());

        $request->setProtocolVersion('1.0');
        $this->assertEquals('1.0', $request->getProtocolVersion());
    }

    public function testHeaders()
    {
        $this->assertCount(0, $this->request->getHeaders());
        $this->assertFalse($this->request->hasHeader('foo'));
        $this->assertCount(0, $this->request->getHeader('foo'));
        $this->assertSame('', $this->request->getHeaderLine('foo'));

        $request = $this->request->withHeader('foo', 'bar');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals('bar', $request->getHeaderLine('foo'));
        $this->assertCount(1, $request->getHeader('foo'));

        $request = $request->withAddedHeader('foo', 'bar2');
        $this->assertCount(2, $request->getHeader('foo'));

        $request = $request->withoutHeader('foo');
        $this->assertCount(0, $request->getHeader('foo'));
    }

    public function testBody()
    {
        $request = $this->request->withBody(new BodyStream());
        $this->assertNotSame($this->request, $request);
    }
}
