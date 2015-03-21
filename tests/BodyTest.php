<?php
use Fol\Http\BodyStream;

class BodyTest extends PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $body = new BodyStream();

        $this->assertTrue($body->isReadable());
        $this->assertTrue($body->isWritable());
        $this->assertTrue($body->isSeekable());
        $this->assertFalse($body->eof());
        $this->assertSame(0, $body->tell());
        $this->assertSame(0, $body->getSize());
        $this->assertSame('', $body->read(1024));
        $this->assertSame('', (string) $body);
        $this->assertTrue($body->eof());

        $body->write('Hello world');
        $body->seek(0);

        $this->assertSame('Hello', $body->read(5));
        $this->assertSame(' world', $body->read(6));
        $this->assertSame(11, $body->tell());
        $this->assertSame(11, $body->getSize());
        $this->assertSame('', $body->getContents());
        $this->assertSame('Hello world', (string) $body);

        $body->seek(0);
        $this->assertSame('Hello world', $body->getContents());

        $body->close();
        $this->assertFalse($body->isReadable());
        $this->assertFalse($body->isWritable());
        $this->assertFalse($body->isSeekable());
    }
}
