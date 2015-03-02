<?php
/**
 * Fol\Http\Response.
 *
 * Class to manage the http response data
 */

namespace Fol\Http;

class Response extends Message
{
    public $cookies;

    private $statusCode;
    private $reasonPhrase;

    /**
     * Constructor.
     *
     * @param string  $content The body of the response
     * @param integer $status  The status code (200 by default)
     * @param array   $headers The headers to send in the response
     */
    public function __construct($content = '', $status = 200, array $headers = array())
    {
        $this->setBody(new Body());

        if ($content) {
            $this->getBody()->write($content);
        }

        $this->setStatus($status);

        $this->headers = new Headers($headers);
        $this->cookies = new ResponseCookies();
    }

    /**
     * Magic function to clone the internal objects.
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->cookies = clone $this->cookies;
    }

    /**
     * Magic function to converts the current response to a string.
     */
    public function __toString()
    {
        return sprintf('HTTP/%s %s %s', $this->protocol, $this->statusCode, $this->reasonPhrase)
            ."\nCookies:\n".$this->cookies
            ."\nHeaders:\n".$this->headers

            ."\nBody:\n"
            ."\n\n".$this->getBody();
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($code, $reasonPhrase = null)
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase ?: Utils::getReasonPhrase($code);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Set the status code and header needle to redirect to another url.
     *
     * @param string  $url    The url of the new location
     * @param integer $status The http code to redirect (302 by default)
     */
    public function redirect($url, $status = 302)
    {
        $this->setStatus($status);
        $this->headers->set('location', $url);
    }

    /**
     * Send the response to the client.
     */
    public function send()
    {
        if (!headers_sent()) {
            header(sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatusCode(), $this->getReasonPhrase()));

            foreach ($this->headers->getAsString() as $header) {
                header($header, false);
            }

            foreach ($this->cookies->get() as $cookie) {
                if (!setcookie($cookie['name'], $cookie['value'], $cookie['expires'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly'])) {
                    throw new \Exception('Error sending the cookie '.$cookie['name']);
                }
            }
        }

        $level = ob_get_level();

        while ($level > 0) {
            ob_end_flush();
            $level--;
        }

        $body = $this->getBody();
        $body->seek(0);

        while (!$body->eof()) {
            echo $body->read(1024);
            flush();
        }

        $body->close();
    }
}
