<?php
/**
 * Fol\Http\Message
 *
 * Class to manage a http message
 */
namespace Fol\Http;

abstract class Message
{
    public $headers;

    protected $protocol = '1.1';
    protected $body;


    /**
     * {inheritDoc}
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
     * Returns the message body
     *
     * @return Body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the message body
     *
     * @param Body $body
     */
    public function setBody(Body $body)
    {
        $this->body = $body;
    }
}
