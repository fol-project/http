<?php
namespace Fol\Http;

use Psr\Http\Message\ServerRequestInterface;
use Fol\Bag;

/**
 * Class to manage the http request data
 */
class Request extends Request implements ServerRequestInterface
{
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
            $request->setBody(new BodyStream($globals->getInput(), 'r'));
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
        $this->data = (new Bag())->set($data);
        $this->files = (new Bag())->set($files);
        $this->headers = (new Headers())->set($headers);
        $this->cookies = (new RequestCookies())->set($cookies);
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
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function getServerParams()
    {
        return [];
    }

    /**
     * Set new cookies
     * 
     * @param array $cookies
     */
    public function setCookieParams(array $cookies)
    {
        $this->cookies->delete();
        $this->cookies->set($cookies);
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function getCookieParams()
    {
        return $this->cookies->get();
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $copy = clone $this;
        $copy->setCookieParams($cookies);

        return $copy;
    }

    /**
     * Set new query params
     * 
     * @param array $query
     */
    public function setQueryParams(array $query)
    {
        $this->query->delete();
        $this->query->set($query);
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->query->get();
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $copy = clone $this;
        $copy->setQueryParams($query);

        return $copy;
    }

    /**
     * Set new uploaded files
     *
     * @param array $files
     */
    public function setUploadedFiles(array $files)
    {
        $this->files->delete();
        $this->files->set($files);
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function getUploadedFiles()
    {
        return $this->files->get();
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $copy = clone $this;
        $copy->setUploadedFiles($uploadedFiles);
        
        return $copy;
    }

    /**
     * Set parset body
     *
     * @param $data
     */
    public function setParsedBody($data)
    {
        $this->data->delete();

        if ($data) {
            $this->data->set($data);
        }
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->data->get();
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        $copy = clone $this;
        $copy->setParsedBody($data);
        
        return $copy;
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes->get();
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (!$this->attributes->has($name)) {
            return $default;
        }

        return $this->attributes->get($name);
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $copy = clone $this;
        $copy->attributes->set($name, $value);

        return $copy;
    }

    /**
     * @see ServerRequestInterface
     *
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $copy = clone $this;
        $copy->attributes->delete($name);

        return $copy;
    }
}
