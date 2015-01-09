<?php
/**
 * Fol\Http\Request
 *
 * Class to manage the http request data
 */
namespace Fol\Http;

use Psr\Http\Message\IncomingRequestInterface;
use Psr\Http\Message\OutgoingRequestInterface;

class Request extends Message implements IncomingRequestInterface, OutgoingRequestInterface
{
    protected static $constructors = [];

    protected $method;
    protected $language;

    public $url;
    public $query;
    public $data;
    public $files;
    public $cookies;
    public $route;
    public $attributes;

    /**
     * Creates a new request object from global values
     *
     * @return Request The object with the global data
     */
    public static function createFromGlobals()
    {
        $request = new static(Globals::getUrl(), Globals::getMethod(), Globals::getHeaders(), Globals::getGet(), Globals::getPost(), Globals::getFiles(), Globals::getCookies());

        if (!$request->data->length()) {
            $request->setBodySource('php://input', 'r');
        }

        return $request;
    }


    /**
     * Constructor
     *
     * @param string $url     The request url
     * @param string $method  The request method
     * @param array  $headers The request headers
     * @param array  $query   The url parameters
     * @param array  $data    The request payload data
     * @param array  $files   The FILES parameters
     * @param array  $cookies The request cookies
     */
    public function __construct($url = '', $method = 'GET', array $headers = array(), array $query = array(), array $data = array(), array $files = array(), array $cookies = array())
    {
        $this->url = new Url($url);
        $this->query = $this->url->query;

        $this->setMethod($method);
        $this->query->set($query);

        $this->attributes = new RequestParameters();
        $this->data = new RequestParameters($data);
        $this->files = new RequestFiles($files);

        $this->events = new Events();
        $this->headers = new RequestHeaders($headers);
        $this->cookies = $this->headers->cookies;
        $this->cookies->set($cookies);
    }

    /**
     * Magic function to clone the internal objects
     */
    public function __clone()
    {
        $this->url = clone $this->url;
        $this->query = $this->url->query;
        $this->data = clone $this->data;
        $this->attributes = clone $this->attributes;
        $this->files = clone $this->files;
        $this->cookies = clone $this->cookies;
        $this->headers = clone $this->headers;
    }


    /**
     * Magic function to convert the request to a string
     */
    public function __toString()
    {
        $text = $this->getMethod().' '.$this->getUrl();
        $text .= "\nFormat: ".$this->getFormat();
        $text .= "\nLanguage: ".$this->getLanguage();
        $text .= "\nQuery:\n".$this->query;
        $text .= "\nData:\n".$this->data;
        $text .= "\nFiles:\n".$this->files;
        $text .= "\nCookies:\n".$this->cookies;
        $text .= "\nHeaders:\n".$this->headers;

        if (isset($this->route)) {
            $text .= "\nRoute:\n".$this->route;
        }

        $text .= "\n\n".$this->read();

        return $text;
    }

    /**
     * Set a new url to the request
     *
     * @param string $url The new url
     */
    public function setUrl($url)
    {
        $this->url->setUrl($url);
    }

    /**
     * Returns the full url
     *
     * @param boolean $query True to add the query to the url (false by default)
     *
     * @return string The current url
     */
    public function getUrl()
    {
        return $this->url->getUrl();
    }

    /**
     * {@inheritDoc}
     */
    public function getServerParams()
    {
        return [
            'REQUEST_METHOD' => $this->getMethod(),
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => $this->getHost(),
            'REMOTE_ADDR' => $this->getIp(),
            'HTTP_ACCEPT' => $this->headers->get('Accept'),
            'HTTP_ACCEPT_CHARSET' => $this->headers->get('Accept-Charset'),
            'HTTP_ACCEPT_ENCODING' => $this->headers->get('Accept-Encoding'),
            'HTTP_ACCEPT_LANGUAGE' => $this->headers->get('Accept-Language'),
            'HTTP_CONNECTION' => $this->headers->get('Accept-Connection'),
            'HTTP_REFERER' => $this->headers->get('Referer'),
            'HTTP_USER_AGENT' => $this->headers->get('User-Agent'),
            'HTTPS' => ($this->url->getScheme() === 'https') ? 'on' : 'off',
            'REQUEST_URI' => $this->url->getFullPath()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams()
    {
        return $this->cookies->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams()
    {
        return $this->url->query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFileParams()
    {
        return $this->files->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getBodyParams()
    {
        return $this->data->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes()
    {
        return $this->attributes->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($attribute, $default = null)
    {
        return $this->attributes->get($attribute, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttributes(array $attributes)
    {
        return $this->attributes->set($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttribute($attribute, $value)
    {
        return $this->attributes->set($attribute, $value);
    }

    /**
     * Gets the requested format.
     *
     * @return string The current format (html, xml, css, etc)
     */
    public function getFormat()
    {
        if (($extension = $this->url->getExtension()) && isset(Headers::$formats[$extension])) {
            return $extension;
        }

        foreach (array_keys($this->headers->getParsed('Accept')) as $mimetype) {
            if ($format = Headers::getFormat($mimetype)) {
                return $format;
            }
        }

        return 'html';
    }

    /**
     * Returns the client language
     *
     * @return string The language code
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set the client language
     *
     * @param string $language The language code
     */
    public function setLanguage($language)
    {
        $this->language = strtolower($language);
    }

    /**
     * Gets the preferred language
     *
     * @param array $locales Ordered available languages
     *
     * @param string|null
     */
    public function getPreferredLanguage(array $locales)
    {
        $languages = array_keys($this->headers->getParsed('Accept-Language'));

        if ($locales === null) {
            return isset($languages[0]) ? Headers::getLanguage($languages[0]) : null;
        }

        if (!$languages) {
            return isset($locales[0]) ? Headers::getLanguage($locales[0]) : null;
        }

        $common = array_values(array_intersect($languages, $locales));

        return Headers::getLanguage(isset($common[0]) ? $common[0] : $locales[0]);
    }

    /**
     * Returns all client IPs
     *
     * @return array The client IPs
     */
    public function getIps()
    {
        static $forwarded = [
            'Client-Ip',
            'X-Forwarded-For',
            'X-Forwarded',
            'X-Cluster-Client-Ip',
            'Forwarded-For',
            'Forwarded'
        ];

        $ips = [];

        foreach ($forwarded as $key) {
            if ($this->headers->has($key)) {
                foreach (array_map('trim', explode(',', $this->headers->get($key))) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $ips[] = $ip;
                    }
                }
            }
        }

        return $ips;
    }

    /**
     * Returns the client IP
     *
     * @return string|null The client IP
     */
    public function getIp()
    {
        $ips = $this->getIps();

        return isset($ips[0]) ? $ips[0] : null;
    }

    /**
     * Detects if the request has been made by ajax or not
     *
     * @return boolean TRUE if the request if ajax, FALSE if not
     */
    public function isAjax()
    {
        return (strtolower($this->headers->get('X-Requested-With')) === 'xmlhttprequest') ? true : false;
    }

    /**
     * Gets the request method
     *
     * @return string The request method (in uppercase: GET, POST, etc)
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the request method
     *
     * @param string $method The request method (GET, POST, etc)
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Gets the request username
     *
     * @return string|null
     */
    public function getUser()
    {
        $authentication = $this->headers->getAuthentication();

        return isset($authentication['username']) ? $authentication['username'] : $this->url->getUser();
    }

    /**
     * Gets the request password
     *
     * @return string
     */
    public function getPassword()
    {
        $authentication = $this->headers->getAuthentication();

        return isset($authentication['password']) ? $authentication['password'] : $this->url->getPassword();
    }

    /**
     * Validate the user password in a digest authentication
     *
     * @param string $password
     * @param string $realm
     *
     * @return boolean
     */
    public function checkPassword($password, $realm)
    {
        $authentication = $this->headers->getAuthentication();

        if (empty($authentication['type']) || $authentication['type'] !== 'Digest') {
            return false;
        }

        $method = $this->getMethod();

        $A1 = md5("{$authentication['username']}:{$realm}:{$password}");
        $A2 = md5("{$method}:{$authentication['uri']}");

        $validResponse = md5("{$A1}:{$authentication['nonce']}:{$authentication['nc']}:{$authentication['cnonce']}:{$authentication['qop']}:{$A2}");

        return ($authentication['response'] === $validResponse);
    }
}
