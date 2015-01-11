<?php
/**
 * Fol\Http\Body
 *
 * Class store a message body
 */
namespace Fol\Http;

class Body
{
    protected $stream;
    protected $source;

    private static $readWriteHash = [
        'read' => ['r', 'w+', 'r+', 'x+', 'c+', 'rb', 'w+b', 'r+b', 'x+b', 'c+b', 'rt', 'w+t', 'r+t', 'x+t', 'c+t', 'a+'],
        'write' => ['w', 'w+', 'rw', 'r+', 'x+', 'c+', 'wb', 'w+b', 'r+b', 'x+b', 'c+b', 'w+t', 'r+t', 'x+t', 'c+t', 'a', 'a+']
    ];


    /**
     * Constructor
     *
     * @param string|resource $stream The stream resouce opened with fopen or the path
     * @param string          $mode   If you provide a path, the open mode used
     */
    public function __construct($stream = 'php://temp', $mode = 'r+')
    {
        if (is_resource($stream)) {
            $this->stream = $stream;
        } else {
            $this->source = [$stream, $mode];
        }
    }

    /**
     * Open and returns the stream resource
     *
     * @return resource
     */
    protected function getStream()
    {
        if ($this->stream) {
            return $this->stream;
        }

        return $this->stream = fopen($this->source[0], $this->source[1]);
    }


    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $this->seek(0);

        return stream_get_contents($this->getStream(), -1);
    }


    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return fclose($this->getStream());
    }


    /**
     * {@inheritDoc}
     */
    public function detach()
    {
        $stream = $this->getStream();
        $this->stream = null;

        return $stream;
    }


    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        $stats = fstat($this->getStream());

        if (isset($stats['size'])) {
            return $stats['size'];
        }
    }


    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        return ftell($this->getStream());
    }


    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        return feof($this->getStream());
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
        return fseek($this->getStream(), $offset, $whence);
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
        return fwrite($this->getStream(), $string);
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
        return fread($this->getStream(), $length);
    }


    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        return stream_get_contents($this->getStream());
    }


    /**
     * {@inheritDoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = @stream_get_meta_data($this->getStream());

        if ($key) {
            return isset($metadata[$key]) ? $metadata[$key] : null;
        }

        return $metadata;
    }
}
