<?php
namespace Fol\Http;

use Psr\Http\Message\UriInterface;
use Fol\Bag;

/**
 * Class to represent and manipulate urls
 */
class Uri implements UriInterface
{
    protected static $defaultPorts = [
        'http' => 80,
        'https' => 443,
    ];

    protected $scheme;
    protected $host;
    protected $port;
    protected $user;
    protected $password;
    protected $directory;
    protected $filename;
    protected $extension;
    protected $fragment;

    public $query;

    /**
     * Generates an url using its parts.
     *
     * @param string       $scheme
     * @param string       $host
     * @param integer|null $port
     * @param string       $user
     * @param string       $password
     * @param string       $path
     * @param string       $query
     * @param string       $fragment
     *
     * @return string
     */
    public static function build($scheme = '', $host = '', $port = null, $user = '', $password = '', $path = '', $query = '', $fragment = '')
    {
        $url = $scheme ? sprintf('%s:', $scheme) : '';

        if ($user) {
            if ($url) {
                $url .= '//';
            }

            $url .= sprintf('%s%s', $user, $password ? sprintf(':%s', $password) : '');
            $url .= $host ? sprintf('@%s', $host) : '';
        } else if ($host) {
            $url .= $host ? sprintf('//%s', $host) : '';
        }

        if ($port) {
            $url .= sprintf(':%d', $port);
        }

        if ($path) {
            $url .= $path;
        }

        if ($query) {
            $url .= sprintf('?%s', $query);
        }

        if ($fragment) {
            $url .= sprintf('#%s', $fragment);
        }

        return $url;
    }

    /**
     * Constructor.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->query = new Bag();

        $url = parse_url($url);

        $this->setScheme(isset($url['scheme']) ? $url['scheme'] : '');
        $this->setHost(isset($url['host']) ? $url['host'] : '');
        $this->setPort(isset($url['port']) ? $url['port'] : null);
        $this->setUser(isset($url['user']) ? $url['user'] : '');
        $this->setPassword(isset($url['password']) ? $url['password'] : '');
        $this->setPath(isset($url['path']) ? $url['path'] : '');
        $this->setQuery(isset($url['query']) ? $url['query'] : '');
        $this->setFragment(isset($url['fragment']) ? $url['fragment'] : '');
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function __toString()
    {
        return self::build($this->getScheme(), $this->getHost(), $this->getPort(), $this->getUser(), $this->getPassword(), $this->getPath(), $this->getQuery(), $this->getFragment());
    }

    /**
     * Magic function to clone the internal objects.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }

    /**
     * Sets the url scheme.
     *
     * @param null|string $scheme
     * 
     * @return $this
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme ? strtolower($scheme) : $scheme;

        return $this;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $copy = clone $this;
        $copy->setScheme($scheme);

        return $copy;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        return static::build(null, $this->getHost(), $this->getPort(), $this->getUser(), $this->getPassword());
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return static::build(null, null, null, $this->getUser(), $this->getPassword());
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $copy = clone $this;
        $copy->setUser($user);
        $copy->setPassword($password);

        return $copy;
    }

    /**
     * Sets the url host.
     *
     * @param null|string $host
     * 
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host ? strtolower($host) : $host;

        return $this;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $copy = clone $copy;
        $copy->setHost($host);

        return $copy;
    }

    /**
     * Sets the url port.
     *
     * @param null|integer $port
     * 
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = ($port === null) ? $port : intval($port);

        return $this;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getPort()
    {
        if (isset(static::$defaultPorts[$this->scheme]) && (static::$defaultPorts[$this->scheme] == $this->port)) {
            return null;
        }

        return $this->port;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $copy = clone $this;
        $copy->setPort($port);

        return $copy;
    }

    /**
     * Sets the url path.
     *
     * @param string $path
     * 
     * @return $this
     */
    public function setPath($path)
    {
        $parts = pathinfo(urldecode($path)) + ['dirname' => '/', 'filename' => '', 'extension' => ''];

        $this->setDirectory($parts['dirname']);
        $this->setFilename($parts['filename']);
        $this->setExtension($parts['extension']);

        return $this;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getPath()
    {
        $path = $this->directory;

        if ($this->filename) {
            if ($path && ($path !== '/')) {
                $path .= '/';
            }

            $path .= $this->filename;

            if ($this->extension) {
                $path .= ".{$this->extension}";
            }
        }

        return $path;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $copy = clone $this;
        $copy->setPath($path);

        return $copy;
    }

    /**
     * Set a new query
     *
     * @param string $query
     * 
     * @return $this
     */
    public function setQuery($query)
    {
        parse_str(html_entity_decode($query), $values);

        $this->query->delete();
        $this->query->set($values);

        return $this;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return count($this->query) ? http_build_query($this->query->get()) : '';
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $copy = clone $this;
        $copy->setQuery($query);

        return $copy;
    }

    /**
     * Sets the url fragment.
     *
     * @param string $fragment
     * 
     * @return $this
     */
    public function setFragment($fragment)
    {
        if ($fragment && $fragment[0] === '#') {
            $fragment = substr($fragment, 1);
        }

        $this->fragment = $fragment;

        return $this;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @see UriInterface
     *
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $copy = clone $this;
        $copy->setFragment($fragment);

        return $copy;
    }

    /**
     * Gets the url user.
     *
     * @return null|string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the url user.
     *
     * @param null|string $user
     * 
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the url password.
     *
     * @return null|string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the url password.
     *
     * @param null|string $password
     * 
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Gets the url extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Sets the url extension.
     *
     * @param string $extension
     * 
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = strtolower($extension);

        return $this;
    }

    /**
     * Gets the url filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Sets the url filename.
     *
     * @param string $filename
     * 
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Gets the url directory.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Sets the url directory.
     *
     * @param string $directory
     * 
     * @return $this
     */
    public function setDirectory($directory)
    {
        if ($directory === '.' || strlen($directory) === 0) {
            $this->directory = '/';

            return $this;
        }

        $directory = '/'.str_replace('\\', '/', $directory);

        $replace = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];

        do {
            $directory = preg_replace($replace, '/', $directory, -1, $n);
        } while ($n > 0);

        $this->directory = $directory;

        return $this;
    }

    /**
     * Returns the url parameters as array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'scheme' => $this->getScheme(),
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'user' => $this->getUser(),
            'password' => $this->getPassword(),
            'path' => $this->getPath(),
            'query' => $this->query->get(),
            'fragment' => $this->getFragment(),
        ];
    }
}
