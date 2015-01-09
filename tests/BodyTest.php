<?php
use Fol\Http\Body;

require_once dirname(__DIR__).'/vendor/autoload.php';

class BodyTest extends PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $body = new Body();

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
        $this->assertSame('Hello world', (string) $body);
        $this->assertSame('', $body->getContents());

        $body->seek(0);
        $this->assertSame('Hello world', $body->getContents());
    }
}
