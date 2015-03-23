<?php
namespace Fol\Http;

/**
 * Class store a message body stream
 */
class BodyCallback implements BodyInterface
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
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function write($string)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents()
    {
        if ($this->callback) {
            return call_user_func($this->callback);
        }

        return '';
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
