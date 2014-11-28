<?php
/**
 * Fol\Http\ResponseHeaders
 *
 * Manage http response headers
 */
namespace Fol\Http;

class ResponseHeaders extends Headers
{
    public $cookies;

    /**
     * List of standard http status codes
     */
    public static $status = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];


    /**
     * Gets the reason phrase related with a status code.
     *
     * @param integer $code The Http code
     *
     * @return null|string
     */
    public static function getDefaultReasonPhrase($code)
    {
        return isset(self::$status[$code]) ? self::$status[$code] : null;
    }

    /**
     * Constructor
     *
     * @param array $items The items to store
     */
    public function __construct(array $items = null)
    {
        parent::__construct($items);

        $this->cookies = new ResponseCookies();
    }

    /**
     * {@inheritDoc}
     */
    public function setFromString($string, $replace = true)
    {
        parent::setFromString($string, $replace);

        if (strpos($string, 'Set-Cookie:') === 0) {
            $this->cookies->setFromString($string);
        }
    }

    /**
     * Sends the headers to the browser
     */
    public function send()
    {
        foreach ($this->getAsString() as $header) {
            header($header, false);
        }

        $this->cookies->send();
    }
}
