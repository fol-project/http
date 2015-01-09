<?php
/**
 * Fol\Http\Response
 *
 * Class to manage the http response data
 */
namespace Fol\Http;

class Response extends Message
{
    protected static $constructors = [];

    public $cookies;

    private $statusCode;
    private $reasonPhrase;

    /**
     * Constructor
     *
     * @param string  $content The body of the response
     * @param integer $status  The status code (200 by default)
     * @param array   $headers The headers to send in the response
     */
    public function __construct ($content = '', $status = 200, array $headers = array())
    {
        $this->setBodySource('php://temp', 'r+');

        if ($content) {
            $this->getBody()->write($content);
        }

        $this->setStatus($status);

        $this->headers = new ResponseHeaders($headers);
        $this->cookies = $this->headers->cookies;

        $this->events = new Events();
        $this->events->on('prepare', [$this, 'prepare']);
    }

    /**
     * Magic function to clone the internal objects
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->cookies = clone $this->cookies;
    }

    /**
     * Magic function to converts the current response to a string
     */
    public function __toString()
    {
        $text = sprintf('HTTP/%s %s %s', $this->protocol, $this->statusCode, $this->reasonPhrase);
        $text .= "\nCookies:\n".$this->cookies;
        $text .= "\nHeaders:\n".$this->headers;
        $text .= "\n\n".$this->read();

        return $text;
    }

    /**
     * Sets the request format
     *
     * @param string $format The new format value
     */
    public function setFormat($format)
    {
        if ($mimetype = Headers::getMimeType($format)) {
            $this->headers->set('Content-Type', "$mimetype; charset=UTF-8");
        }
    }

    /**
     * Sets the request language
     *
     * @param string $language The new language
     */
    public function setLanguage($language)
    {
        $this->headers->set('Content-Language', $language);
    }

    /**
     * Set basic authentication
     *
     * @param string $realm
     */
    public function setBasicAuthentication($realm)
    {
        $this->setStatus(401);
        $this->headers->set('WWW-Authenticate', 'Basic realm="'.$realm.'"');
    }

    /**
     * Set digest authentication
     *
     * @param string      $realm
     * @param string|null $nonce
     */
    public function setDigestAuthentication($realm, $nonce = null)
    {
        $this->setStatus(401);

        if (!$nonce) {
            $nonce = uniqid();
        }

        $this->headers->set('WWW-Authenticate', 'Digest realm="'.$realm.'",qop="auth",nonce="'.$nonce.'",opaque="'.md5($realm).'"');
    }


    /**
     * {@inheritDoc}
     */
    public function setStatus($code, $reasonPhrase = null)
    {
        $this->status = $code;
        $this->reasonPhrase = $reasonPhrase ?: ResponseHeaders::getDefaultReasonPhrase($code);
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
    public function getReasonPhrase($text = false)
    {
        return $this->reasonPhrase;
    }


    /**
     * Set the status code and header needle to redirect to another url
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
     * Sends the response to the client
     */
    public function send()
    {
        if (!headers_sent()) {
            header(sprintf('HTTP/%s %s %s', $this->protocol, $this->status[0], $this->status[1]));

            $this->headers->send();
            $this->cookies->send();
        }

        $level = ob_get_level();

        while ($level > 0) {
            ob_end_flush();
            $level--;
        }

        flush();

        $body = $this->getBody();
        $body->seek(0);

        while (!$body->eof()) {
            echo $body->read(1024);
            flush();
        }

        $body->close();
    }


    /**
     * Default "prepare" listener for the response
     *
     * @param Request $request
     * @param Response $response
     * @param Router\Route $route
     */
    public function prepare(Request $request, Response $response, Router\Route $route)
    {
        if (!$this->headers->has('Content-Type') && ($format = $request->getFormat())) {
            $this->setFormat($format);
        }

        if (!$this->headers->has('Content-Language') && ($language = $request->getLanguage())) {
            $this->setLanguage($language);
        }

        if ($this->headers->has('Transfer-Encoding')) {
            $this->headers->delete('Content-Length');
        }

        if (!$this->headers->has('Date')) {
            $this->headers->setDateTime('Date', new \DateTime());
        }

        if ($request->getMethod() === 'HEAD') {
            $this->setBody(new Body(fopen('php://temp', 'r')));
        }
    }
}
