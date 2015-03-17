<?php
namespace Fol\Http;

use Fol\Bag;

/**
 * Class to represent and manipulate urls
 */
class Url
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
     * @param string      $scheme
     * @param string      $host
     * @param integer     $port
     * @param string|null $user
     * @param string|null $password
     * @param string      $path
     * @param array       $query
     * @param string      $fragment
     *
     * @return string
     */
    public static function build($scheme, $host, $port, $user, $password, $path, array $query = null, $fragment = null)
    {
        if (isset(self::$defaultPorts[$scheme]) && (self::$defaultPorts[$scheme] == $port)) {
            $port = null;
        }

        return sprintf('%s%s%s%s%s%s%s',
            $scheme ? sprintf('%s:', $scheme) : '',
            $user ? sprintf('%s%s@', $user, $password ? sprintf(':%s', $password) : '') : '',
            $host ? sprintf('//%s', $host) : '',
            $port ? sprintf(':%d', $port) : '',
            $path,
            $query ? '?'.http_build_query($query) : '',
            $fragment ? '#'.$fragment : ''
        );
    }

    /**
     * Constructor.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->query = new Bag();
        $this->setUrl($url);
    }

    /**
     * Set a new url.
     *
     * @param string $url The new url
     */
    public function setUrl($url)
    {
        $url = parse_url($url);

        $this->setScheme(isset($url['scheme']) ? $url['scheme'] : null);
        $this->setHost(isset($url['host']) ? $url['host'] : null);
        $this->setPort(isset($url['port']) ? $url['port'] : null);
        $this->setUser(isset($url['user']) ? $url['user'] : null);
        $this->setPassword(isset($url['password']) ? $url['password'] : null);
        $this->setPath(isset($url['path']) ? $url['path'] : '');
        $this->setFragment(isset($url['fragment']) ? $url['fragment'] : '');

        if (isset($url['query'])) {
            parse_str(html_entity_decode($url['query']), $query);

            $this->query->set($query);
        }
    }

    /**
     * Magic method to stringify the url.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUrl(true, true);
    }

    /**
     * Gets the url.
     *
     * @param boolean $query    True to add the query to the url (false by default)
     * @param boolean $fragment True to add the fragment to the url (false by default)
     *
     * @return string
     */
    public function getUrl($query = false, $fragment = false)
    {
        return self::build($this->getScheme(), $this->getHost(), $this->getPort(), $this->getUser(), $this->getPassword(), $this->getPath(), ($query ? $this->query->get() : []), ($fragment ? $this->getFragment() : ''));
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
     */
    public function setDirectory($directory)
    {
        $directory = str_replace('\\', '/', $directory);

        if ($directory === '.') {
            $directory = '/';
        } elseif ($directory[0] !== '/') {
            $directory = "/{$directory}";
        }

        $this->directory = $directory;
    }

    /**
     * Gets the url path (directory + filename [+ extension]).
     *
     * @return string
     */
    public function getPath($extension = true)
    {
        $path = $this->directory;

        if ($this->filename) {
            if ($path !== '/') {
                $path .= '/';
            }

            $path .= $this->filename;

            if ($extension && $this->extension) {
                $path .= ".{$this->extension}";
            }
        }

        return $path;
    }

    /**
     * Sets the url path.
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $parts = pathinfo(urldecode($path)) + ['dirname' => '/', 'filename' => '', 'extension' => ''];

        $this->setDirectory($parts['dirname']);
        $this->setFilename($parts['filename']);
        $this->setExtension($parts['extension']);
    }

    /**
     * Gets the url scheme (for example: http).
     *
     * @return null|string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Sets the url scheme.
     *
     * @param null|string $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme ? strtolower($scheme) : $scheme;
    }

    /**
     * Gets the url host.
     *
     * @return null|string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the url host.
     *
     * @param null|string $host
     */
    public function setHost($host)
    {
        $this->host = $host ? strtolower($host) : $host;
    }

    /**
     * Gets the url port.
     *
     * @return null|integer
     */
    public function getPort()
    {
        return $this->port ?: (isset(self::$defaultPorts[$this->scheme]) ? self::$defaultPorts[$this->scheme] : null);
    }

    /**
     * Sets the url port.
     *
     * @param null|integer $port
     */
    public function setPort($port)
    {
        $this->port = ($port === null) ? $port : intval($port);
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
     */
    public function setUser($user)
    {
        $this->user = $user;
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
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Gets the url fragment.
     *
     * @return null|string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Sets the url fragment.
     *
     * @param null|string $fragment
     */
    public function setFragment($fragment)
    {
        if ($fragment && $fragment[0] === '#') {
            $fragment = substr($fragment, 1);
        }

        $this->fragment = $fragment;
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
     */
    public function setExtension($extension)
    {
        $this->extension = strtolower($extension);
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
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
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
            'path' => $this->getPath(true),
            'query' => $this->query->get(),
            'fragment' => $this->getFragment(),
        ];
    }
}
