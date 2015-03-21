<?php
namespace Fol\Http;

/**
 * Class store a message body stream
 */
class BodyStream implements BodyInterface
{
    protected $stream;
    protected $source;
    protected $sendPosition = 0;

    private static $readWriteHash = [
        'read' => ['r', 'w+', 'r+', 'x+', 'c+', 'rb', 'w+b', 'r+b', 'x+b', 'c+b', 'rt', 'w+t', 'r+t', 'x+t', 'c+t', 'a+'],
        'write' => ['w', 'w+', 'rw', 'r+', 'x+', 'c+', 'wb', 'w+b', 'r+b', 'x+b', 'c+b', 'w+t', 'r+t', 'x+t', 'c+t', 'a', 'a+'],
    ];

    /**
     * Constructor.
     *
     * @param string|resource $stream The stream resouce opened with fopen or the path
     * @param string          $mode   If you provide a path, the open mode used
     */
    public function __construct($stream = 'php://temp', $mode = 'r+')
    {
        $this->attach($stream, $mode);
    }

    /**
     * Open and returns the stream resource.
     *
     * @return null|resource
     */
    protected function getStream()
    {
        if ($this->stream) {
            return $this->stream;
        }

        if (empty($this->source)) {
            return;
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
        $this->source = [];

        return $stream;
    }

    /**
     * @param string|resource $stream The stream resouce opened with fopen or the path
     * @param string          $mode   If you provide a path, the open mode used
     */
    public function attach($stream, $mode = 'r')
    {
        if (is_resource($stream)) {
            $this->stream = $stream;
            $this->source = [];
        } else {
            $this->stream = null;
            $this->source = [$stream, $mode];
        }

        return $stream;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        if (!$this->getStream()) {
            return;
        }

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
        if (!$this->getStream()) {
            return false;
        }

        return ftell($this->getStream());
    }

    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        if (!$this->getStream()) {
            return false;
        }

        return feof($this->getStream());
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable()
    {
        if (!$this->getStream()) {
            return false;
        }

        return (bool) $this->getMetadata('seekable');
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($this->isSeekable()) {
            return fseek($this->getStream(), $offset, $whence) === 0;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable()
    {
        if (!$this->getStream()) {
            return false;
        }

        return in_array($this->getMetadata('mode'), self::$readWriteHash['write']);
    }

    /**
     * {@inheritDoc}
     */
    public function write($string)
    {
        if ($this->isWritable()) {
            return fwrite($this->getStream(), $string);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable()
    {
        if (!$this->getStream()) {
            return false;
        }

        return in_array($this->getMetadata('mode'), self::$readWriteHash['read']);
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        if ($this->isReadable()) {
            return fread($this->getStream(), $length);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        if ($this->isReadable()) {
            return stream_get_contents($this->getStream());
        }

        return '';
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

    /**
     * {@inheritdoc}
     */
    public function send()
    {
        $this->seek($this->sendPosition);

        while (!$this->eof()) {
            echo $this->read(1024);
            flush();
        }

        $this->sendPosition = $this->tell();
    }
}
