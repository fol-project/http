<?php
/**
 * Fol\Http\Request
 *
 * Class to manage the http request data
 */
namespace Fol\Http;

class Request extends Message
{
    protected $handler;
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
     * @param array  $files   The uploaded files
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
        $this->headers = new Headers($headers);
        $this->cookies = new RequestCookies($cookies);
    }

    /**
     * Magic function to clone the internal objects
     */
    public function __clone()
    {
        $this->url = clone $this->url;
        $this->query = $this->url->query;
        $this->attributes = clone $this->attributes;
        $this->data = clone $this->data;
        $this->files = clone $this->files;
        $this->headers = clone $this->headers;
        $this->cookies = clone $this->cookies;
    }

    /**
     * Magic function to get registered services.
     *
     * @param string $name The name of the service
     *
     * @return string The service instance or null
     */
    public function __get($name)
    {
        if ($this->handler) {
            return $this->handler->$name;
        }
    }


    /**
     * Magic function to convert the request to a string
     */
    public function __toString()
    {
        return $this->getMethod().' '.$this->getUrl()

            ."\nFormat: ".$this->getFormat()
            ."\nLanguage: ".$this->getLanguage()
            ."\nQuery:\n".$this->query
            ."\nData:\n".$this->data
            ."\nFiles:\n".$this->files
            ."\nCookies:\n".$this->cookies
            ."\nHeaders:\n".$this->headers

            ."\nBody:\n"
            ."\n\n".$this->getBody();
    }

    /**
     * Set the handler of this request
     *
     * @param RequestHandler $handler
     */
    public function setHandler(RequestHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Get the handler of this request
     *
     * @return null|RequestHandler
     */
    public function getHandler()
    {
        return $this->handler;
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
     * Gets the requested format.
     *
     * @return string The current format (html, xml, css, etc)
     */
    public function getFormat()
    {
        if (($extension = $this->url->getExtension()) && Utils::formatToMimetype($extension)) {
            return $extension;
        }

        foreach (array_keys(Utils::parseHeader($this->headers->get('Accept'))) as $mimetype) {
            if ($format = Utils::mimetypeToFormat($mimetype)) {
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
     * @param null|array $locales Ordered available languages
     *
     * @param string|null
     */
    public function getPreferredLanguage(array $locales = null)
    {
        $languages = array_keys(Utils::parseHeader($this->headers->get('Accept-Language')));

        if ($locales === null) {
            return isset($languages[0]) ? Utils::getLanguage($languages[0]) : null;
        }

        if (!$languages) {
            return isset($locales[0]) ? Utils::getLanguage($locales[0]) : null;
        }

        $common = array_values(array_intersect($languages, $locales));

        return Utils::getLanguage(isset($common[0]) ? $common[0] : $locales[0]);
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
        $authentication = Utils::parseAuthorizationHeader($this->headers->get('Authorization'));

        return isset($authentication['username']) ? $authentication['username'] : $this->url->getUser();
    }

    /**
     * Gets the request password
     *
     * @return string
     */
    public function getPassword()
    {
        $authentication = Utils::parseAuthorizationHeader($this->headers->get('Authorization'));

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
        $authentication = Utils::parseAuthorizationHeader($this->headers->get('Authorization'));

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
