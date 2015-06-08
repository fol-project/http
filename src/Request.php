<?php
namespace Fol\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Fol\Bag;

/**
 * Class to manage the http request data
 */
class Request extends Message implements RequestInterface
{
    protected $method;
    protected $requestTarget;

    public $uri;

    /**
     * Constructor.
     *
     * @param string $uri     The request uri
     * @param string $method  The request method
     * @param array  $headers The request headers
     */
    public function __construct($uri = '', $method = 'GET', array $headers = array())
    {
        $this->uri = new Uri($uri);
        $this->setMethod($method);
        $this->headers = (new Headers())->set($headers);
    }

    /**
     * Magic function to clone the internal objects.
     */
    public function __clone()
    {
        $this->uri = clone $this->uri;
        $this->headers = clone $this->headers;
    }

    /**
     * Magic function to convert the request to a string.
     */
    public function __toString()
    {
        return $this->getMethod().' '.$this->uri
            ."\n"
            .$this->headers

            ."\n\n"
            .$this->body;
    }

    /**
     * Set a new request target
     * 
     * @param string $requestTarget
     */
    public function setRequestTarget($requestTarget)
    {
        $this->requestTarget = $requestTarget;
    }

    /**
     * @see RequestInterface
     *
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        $query = $this->uri->getQuery();

        if ($query) {
            $target .= "?{$query}";
        }

        return $target;
    }

    /**
     * @see RequestInterface
     *
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        $copy = clone $this;
        $copy->setRequestTarget($requestTarget);

        return $copy;
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

    /**
     * @see RequestInterface
     *
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @see RequestInterface
     *
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $copy = clone $this;
        $copy->setMethod($method);

        return $copy;
    }

    /**
     * Set a new uri
     *
     * @param UriInterface $uri
     * @param boolean      $preseverHost
     */
    public function setUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;

        if (!$preserveHost) {
            $this->headers->set('Host', $uri->getHost());
        }
    }

    /**
     * @see RequestInterface
     *
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @see RequestInterface
     *
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $copy = clone $this;
        $copy->setUri($uri, $preserveHost);

        return $copy;
    }

    /**
     * Detects if the request has been made by ajax or not.
     *
     * @return boolean TRUE if the request if ajax, FALSE if not
     */
    public function isAjax()
    {
        return (strtolower($this->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') ? true : false;
    }
}
