<?php
namespace Fol\Http;

use Fol\Bag;

/**
 * Class to manage the http request data
 */
class Request extends Message
{
    protected $method;

    public $url;
    public $query;
    public $data;
    public $files;
    public $cookies;
    public $attributes;

    /**
     * Creates a new request object from global values.
     *
     * @param Globals $globals
     *
     * @return Request The object with the global data
     */
    public static function createFromGlobals(Globals $globals = null)
    {
        if (!$globals) {
            $globals = new Globals();
        }

        $request = new static($globals->getUrl(), $globals->getMethod(), $globals->getHeaders(), $globals->getGet(), $globals->getPost(), $globals->getFiles(), $globals->getCookies());

        if (!$request->data->count()) {
            $request->setBody(new Body($globals->getInput(), 'r'));
        }

        return $request;
    }

    /**
     * Constructor.
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

        $this->attributes = new Bag();
        $this->data = (new Bag)->set($data);
        $this->files = (new Bag)->set($files);
        $this->headers = (new Headers)->set($headers);
        $this->cookies = (new RequestCookies)->set($cookies);
    }

    /**
     * Magic function to clone the internal objects.
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
     * Magic function to convert the request to a string.
     */
    public function __toString()
    {
        return $this->getMethod().' '.$this->url

            ."\nQuery:\n".$this->query
            ."\nData:\n".$this->data
            ."\nFiles:\n".$this->files
            ."\nCookies:\n".$this->cookies
            ."\nHeaders:\n".$this->headers

            ."\nBody:\n"
            ."\n\n".$this->getBody();
    }

    /**
     * Detects if the request has been made by ajax or not.
     *
     * @return boolean TRUE if the request if ajax, FALSE if not
     */
    public function isAjax()
    {
        return (strtolower($this->headers->get('X-Requested-With')) === 'xmlhttprequest') ? true : false;
    }

    /**
     * Gets the request method.
     *
     * @return string The request method (in uppercase: GET, POST, etc)
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the request method.
     *
     * @param string $method The request method (GET, POST, etc)
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }
}
