<?php
namespace Fol\Http;

/**
 * Class to manage a http message
 */
abstract class Message
{
    protected $protocol = '1.1';

    public $headers;
    public $body;


    /**
     * {inheritDoc}.
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * {@inheritDoc}
     */
    public function setProtocolVersion($version)
    {
        $this->protocol = $version;
    }

    /**
     * Returns the message body.
     *
     * @return BodyInterface
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the message body.
     *
     * @param BodyInterface $body
     */
    public function setBody(BodyInterface $body)
    {
        $this->body = $body;
    }
}
