<?php
use Fol\Http\Stream;

class StreamTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->stream = new Stream('php://temp', 'r+');
    }

    public function testReadWrite()
    {
        $this->assertTrue($this->stream->isReadable());
        $this->assertTrue($this->stream->isWritable());
        $this->assertTrue($this->stream->isSeekable());

        $this->assertFalse($this->stream->eof());
        $this->assertSame(0, $this->stream->tell());
        $this->assertSame(0, $this->stream->getSize());
        $this->assertSame('', $this->stream->read(1024));
        $this->assertSame('', (string) $this->stream);
        $this->assertTrue($this->stream->eof());

        $this->stream->write('Hello world');
        $this->stream->seek(0);

        $this->assertSame('Hello', $this->stream->read(5));
        $this->assertSame(' world', $this->stream->read(6));
        $this->assertSame(11, $this->stream->tell());
        $this->assertSame(11, $this->stream->getSize());
        $this->assertSame('', $this->stream->getContents());
        $this->assertSame('Hello world', (string) $this->stream);

        $this->stream->seek(0);
        $this->assertSame('Hello world', $this->stream->getContents());

        $this->stream->close();
        $this->assertFalse($this->stream->isReadable());
        $this->assertFalse($this->stream->isWritable());
        $this->assertFalse($this->stream->isSeekable());
    }
}
