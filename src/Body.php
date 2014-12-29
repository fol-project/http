<?php
/**
 * Fol\Http\Body
 *
 * Class store a message body
 */
namespace Fol\Http;

use Psr\Http\Message\StreamableInterface;

class Body implements StreamableInterface
{
    protected $stream;

    private static $readWriteHash = [
        'read' => ['r', 'w+', 'r+', 'x+', 'c+', 'rb', 'w+b', 'r+b', 'x+b', 'c+b', 'rt', 'w+t', 'r+t', 'x+t', 'c+t', 'a+'],
        'write' => ['w', 'w+', 'rw', 'r+', 'x+', 'c+', 'wb', 'w+b', 'r+b', 'x+b', 'c+b', 'w+t', 'r+t', 'x+t', 'c+t', 'a', 'a+']
    ];


    /**
     * Constructor
     *
     * @param resource $stream The stream resouce opened with fopen
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }


    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return stream_get_contents($this->stream, -1, 0);
    }


    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return fclose($this->stream);
    }


    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;

        return $stream;
    }


    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            return $stats['size'];
        }
    }


    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        return ftell($this->stream);
    }


    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        return feof($this->stream);
    }


    /**
     * {@inheritDoc}
     */
    public function isSeekable()
    {
        return (bool) $this->getMetadata('seekable');
    }


    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->stream, $offset, $whence);
    }


    /**
     * {@inheritDoc}
     */
    public function isWritable()
    {
        return in_array($this->getMetadata('mode'), self::$readWriteHash['write']);
    }


    /**
     * {@inheritDoc}
     */
    public function write($string)
    {
        return fwrite($this->stream, $string);
    }


    /**
     * {@inheritDoc}
     */
    public function isReadable()
    {
        return in_array($this->getMetadata('mode'), self::$readWriteHash['read']);
    }


    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        return fread($this->stream, $length);
    }


    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        return stream_get_contents($this->stream);
    }


    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->stream);

        if ($key) {
            return isset($metadata[$key]) ? $metadata[$key] : null;
        }

        return $metadata;
    }
}
