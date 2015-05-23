<?php
namespace Fol\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class to manage a http message
 */
abstract class Message implements MessageInterface
{
    protected $protocol = '1.1';

    public $headers;
    public $body;

    /**
     * Set the protocol version
     * 
     * @param string $version
     */
    public function setProtocolVersion($version)
    {
        $this->protocol = $version;
    }

    /**
     * @see MessageInterface
     * 
     * {inheritDoc}.
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $copy = clone $this;
        $copy->setProtocolVersion($version);

        return $copy;
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers->get();
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        return $this->headers[$name] ?: [];
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $copy = clone $this;
        $copy->headers->set($name, $value);

        return $copy;
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $copy = clone $this;

        if ($copy->headers->has($name)) {
            $copy->headers[$name][] = $value;
        } else {
            $copy->headers->set($name, $value);
        }

        return $copy;
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $copy = clone $this;
        $copy->headers->remove($name);

        return $copy;
    }

    /**
     * Sets the message body.
     *
     * @param StreamInterface $body
     */
    public function setBody(StreamInterface $body)
    {
        $this->body = $body;
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @see MessageInterface
     * 
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $copy = clone $this;
        $copy->setBody($body);

        return $this->body;
    }
}
