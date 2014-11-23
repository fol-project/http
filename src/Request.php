<?php
/**
 * Fol\Http\Request
 *
 * Class to manage the http request data
 */
namespace Fol\Http;

use Fol\ServiceContainerTrait;

class Request extends Message
{
    use ServiceContainerTrait;

    protected static $constructors = [];

    protected $method;
    protected $language;
    protected $parent;

    public $url;
    public $query;
    public $data;
    public $files;
    public $cookies;
    public $route;

    /**
     * Creates a new request object from global values
     *
     * @return Request The object with the global data
     */
    public static function createFromGlobals()
    {
        $request = new static(Globals::getUrl(), Globals::getMethod(), Globals::getHeaders(), Globals::getGet(), Globals::getPost(), Globals::getFiles(), Globals::getCookies());

        if (!$request->data->length()) {
            $request->setBody('php://input', true);
        }

        return $request;
    }

    /**
     * Creates a subrequest based in this request
     *
     * @param Request     $request The request instance
     * @param string|null $url     The request url
     * @param string|null $method  The request method
     * @param array|null  $headers The request headers
     * @param array|null  $query   The url parameters
     * @param array|null  $data    The request payload data
     * @param array|null  $files   The FILES parameters
     * @param array|null  $cookies The request cookies
     *
     * @return Request The object with the specified data
     */
    public static function createFromRequest(Request $request, $url = null, $method = null, array $headers = null, array $query = null, array $data = null, array $files = null, array $cookies = null)
    {
        $request = clone $request;

        if ($url !== null) {
            $request->setUrl($url);
        }

        if ($method !== null) {
            $request->setMethod($method);
        }

        if ($headers !== null) {
            $request->headers->set($headers);
        }

        if ($query !== null) {
            $request->query->set($query);
        }

        if ($data !== null) {
            $request->data->set($data);
        }

        if ($files !== null) {
            $request->files->set($files);
        }

        if ($cookies !== null) {
            $request->cookies->set($cookies);
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

        $this->data = new RequestParameters($data);
        $this->files = new RequestFiles($files);

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
        $this->files = clone $this->files;
        $this->cookies = clone $this->cookies;
        $this->headers = clone $this->headers;
    }


    /**
     * Sets the parent request
     *
     * @param Request $request
     */
    public function setParent(Request $request)
    {
        $this->parent = $request;
    }

    /**
     * Gets the parent request
     *
     * @return Request The parent request
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Gets the main parent request
     *
     * @return Request The main request or itself
     */
    public function getMain()
    {
        return $this->parent ? $this->parent->getMain() : $this;
    }

    /**
     * Check whether the request is main or not
     *
     * @return boolean
     */
    public function isMain()
    {
        return empty($this->parent);
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
    public function getUrl($query = false)
    {
        return $this->url->getUrl($query);
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

    /**
     * Sends the request and returns the response
     *
     * @return Response
     */
    public function send()
    {
        if ($this->sendCallback) {
            return call_user_func($this->sendCallback, $this);
        }

        return CurlDispatcher::execute($this);
    }
}
