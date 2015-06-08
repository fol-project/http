<?php
namespace Fol\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class store a stream
 */
class Stream implements StreamInterface
{
    protected $stream;
    protected $source;

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
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function close()
    {
        $resource = $this->detach();

        if ($resource) {
            fclose($resource);
        }
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function detach()
    {
        $stream = $this->getStream();
        $this->stream = null;
        $this->source = [];

        return $stream;
    }

    /**
     * Attach a new stream resource
     * 
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
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
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
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function tell()
    {
        if (!$this->getStream()) {
            throw new RuntimeException('No stream is available');
        }

        $result = ftell($this->getStream());

        if (!is_int($result)) {
            throw new RuntimeException('Error occurred during tell operation');
        }

        return $result;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function eof()
    {
        if (!$this->getStream()) {
            return true;
        }

        return feof($this->getStream());
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        if (!$this->getStream()) {
            return false;
        }

        return (bool) $this->getMetadata('seekable');
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($this->isSeekable()) {
            if (fseek($this->getStream(), $offset, $whence) !== 0) {
                throw new RuntimeException('Error seeking within stream');
            }

            return true;
        }

        throw new RuntimeException('This stream is not seekable');
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function isWritable()
    {
        if (!$this->getStream()) {
            return false;
        }

        $mode = $this->getMetadata('mode');

        return (
            strstr($mode, 'x')
            || strstr($mode, 'w')
            || strstr($mode, 'c')
            || strstr($mode, 'a')
            || strstr($mode, '+')
        );
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('This stream is not writable');
        }

        $result = fwrite($this->getStream(), $string);

        if ($result === false) {
            throw new RuntimeException('Error writing to stream');
        }

        return $result;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function isReadable()
    {
        if (!$this->getStream()) {
            return false;
        }

        $mode = $this->getMetadata('mode');

        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('This stream is not readable');
        }

        $result = fread($this->getStream(), $length);

        if ($result === false) {
            throw new RuntimeException('Error reading stream');
        }

        return $result;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('This stream is not readable');
        }

        $result = stream_get_contents($this->getStream());
        
        if ($result === false) {
            throw new RuntimeException('Error reading from stream');
        }

        return $result;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->getStream());

        if ($key) {
            return isset($metadata[$key]) ? $metadata[$key] : null;
        }

        return $metadata;
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
}
