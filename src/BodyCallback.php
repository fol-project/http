<?php
namespace Fol\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class store a message body callback
 */
class BodyCallback implements StreamInterface
{
    protected $callback;

    /**
     * Constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function detach()
    {
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function getSize()
    {
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function tell()
    {
        throw new RuntimeException('Unable to tell() in a body of type callback');
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function eof()
    {
        return false;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Unable to seek() in a body of type callback');
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function rewind()
    {
        throw new RuntimeException('Unable to rewind() in a body of type callback');
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function write($string)
    {
        throw new RuntimeException('Unable to write() in a body of type callback');
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return false;
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function read($length)
    {
        throw new RuntimeException('Unable to read() in a body of type callback');
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function getContents()
    {
        if ($this->callback) {
            return call_user_func($this->callback);
        }

        return '';
    }

    /**
     * @see StreamInterface
     *
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = [
            'wrapper_data' => null,
            'wrapper_type' => null,
            'stream_type' => 'callback',
            'seekable' => false,
            'unread_bytes' => 0,
            'eof' => false,
            'blocked' => true,
            'timed_out' => false,
            'mode' => 'r',
            'uri' => '',
        ];

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
        echo $this->getContents();
        flush();
    }
}
