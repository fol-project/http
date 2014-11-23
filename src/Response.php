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

    private $status;
    private $headersSent = false;

    /**
     * Constructor
     *
     * @param string  $content The body of the response
     * @param integer $status  The status code (200 by default)
     * @param array   $headers The headers to send in the response
     */
    public function __construct ($content = '', $status = 200, array $headers = array())
    {
        $this->setBody($content);
        $this->setStatus($status);

        $this->headers = new ResponseHeaders($headers);
        $this->cookies = $this->headers->cookies;
        $this->cookies->setDefaults([], BASE_URL);
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
        $text = sprintf('HTTP/1.1 %s', $this->status[0], $this->status[1]);
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
     * Prepare the Response according a request
     *
     * @param Request $request The original request
     */
    public function prepare(Request $request)
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
            $this->setBody('');
        }

        $this->executePrepare($request, $this);

        $request->executePrepare($request, $this);
    }

    /**
     * Sets the status code
     *
     * @param integer $code The status code (for example 404)
     * @param string  $text The status text. If it's not defined, the text will be the defined in the Fol\Http\Headers:$status array
     */
    public function setStatus($code, $text = null)
    {
        $this->status = array($code, ($text ?: Headers::getStatusText($code)));
    }


    /**
     * Gets current status
     *
     * @param string $text Set to TRUE to return the status text instead the status code
     *
     * @return integer The status code or the status text if $text parameter is true
     */
    public function getStatus($text = false)
    {
        return $text ? $this->status[1] : $this->status[0];
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
        if ($this->sendCallback) {
            call_user_func($this->sendCallback, $this);
        } else {
            $this->sendHeaders();
            $this->sendContent();
        }

        static::flush();
    }


    /**
     * Sends the headers if don't have been sent before
     *
     * @return boolean TRUE if the headers are sent and false if headers had been sent before
     */
    public function sendHeaders()
    {
        if (!$this->headersSent) {
            header(sprintf('HTTP/1.1 %s', $this->status[0], $this->status[1]));

            $this->headers->send();
            $this->cookies->send();
            $this->headersSent = true;
        }

        return true;
    }

    /**
     * Sends the content
     */
    public function sendContent()
    {
        static::flush();

        if ($this->isStream()) {
            $body = $this->getBody();

            rewind($body);

            while (!feof($body)) {
                echo fread($body, 1024);
                flush();
            }

            fclose($body);
        } else {
            echo $this->body;
        }
    }

    /**
     * Send the output buffer and empty the response content
     */
    public static function flush()
    {
        $level = ob_get_level();

        while ($level > 0) {
            ob_end_flush();
            $level--;
        }

        flush();
    }
}
