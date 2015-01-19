<?php
/**
 * Fol\Http\Globals
 *
 * Class to detect and return http data from global $_SERVER/$_POST/$_GET/$_FILES arrays
 */
namespace Fol\Http;

class Globals
{
    protected $server;
    protected $get;
    protected $post;
    protected $files;
    protected $cookie;
    protected $input;

    /**
     * Constructor.
     *
     * @param null|array  $server
     * @param null|array  $get
     * @param null|array  $post
     * @param null|array  $files
     * @param null|array  $cookie
     * @param null|string $input
     */
    public function __construct(array $server = null, array $get = null, array $post = null, array $files = null, array $cookie = null, $input = null)
    {
        $this->server = isset($server) ? $server : $_SERVER;
        $this->get = isset($get) ? $get : (array) filter_input_array(INPUT_GET);
        $this->post = isset($post) ? $post : (array) filter_input_array(INPUT_POST);
        $this->files = isset($files) ? $files : $_FILES;
        $this->cookie = isset($cookie) ? $cookie : (array) filter_input_array(INPUT_COOKIE);
        $this->input = isset($input) ? $input : 'php://input';
    }

    /**
     * Gets a value from $server variable
     *
     * @param string $name The parameter name
     *
     * @return mixed The parameter value or null
     */
    protected function get($name)
    {
        return isset($this->server[$name]) ? $this->server[$name] : null;
    }

    /**
     * Checks a value from $_SERVER
     *
     * @param string $name The parameter name
     *
     * @return boolean
     */
    protected function has($name)
    {
        return !empty($this->server[$name]);
    }

    /**
     * Gets the global request scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->get('HTTPS') === 'on' ? 'https' : 'http';
    }

    /**
     * Gets the global request port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->get('X_FORWARDED_PORT') ?: $this->get('SERVER_PORT') ?: 80;
    }

    /**
     * Gets the global request path (with no query)
     *
     * @return string
     */
    public function getPath()
    {
        return explode('?', $this->get('REQUEST_URI'), 2)[0];
    }

    /**
     * Gets the global request host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->get('SERVER_NAME');
    }

    /**
     * Gets the global request url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getScheme().'://'.$this->getHost().':'.$this->getPort().$this->getPath();
    }

    /**
     * Gets the global request method
     *
     * @return string
     */
    public function getMethod()
    {
        $method = $this->get('REQUEST_METHOD');

        if ($method === 'POST' && $this->has('X_HTTP_METHOD_OVERRIDE')) {
            return $this->get('X_HTTP_METHOD_OVERRIDE');
        }

        return $method ?: 'GET';
    }

    /**
     * Gets the global headers
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = [];

        foreach ($this->server as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $headers[str_replace('_', '-', substr($name, 5))] = $value;
                continue;
            }

            if (in_array($name, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                $headers[str_replace('_', '-', $name)] = $value;
            }
        }

        //Http authentication header
        if (!isset($headers['AUTHORIZATION'])) {
            if ($this->has('REDIRECT_HTTP_AUTHORIZATION')) {
                $headers['AUTHORIZATION'] = $this->get('REDIRECT_HTTP_AUTHORIZATION');
            } elseif ($this->has('PHP_AUTH_USER')) {
                $headers['AUTHORIZATION'] = 'Basic '.base64_encode($this->get('PHP_AUTH_USER').':'.$this->has('PHP_AUTH_PW'));
            } elseif ($this->has('PHP_AUTH_DIGEST')) {
                $headers['AUTHORIZATION'] = 'Digest '.$this->get('PHP_AUTH_DIGEST');
            }
        }

        //Ips
        if (!isset($headers['CLIENT-IP']) && !isset($headers['X-FORWARDED-FOR'])) {
            $headers['CLIENT-IP'] = $this->get('REMOTE_ADDR');
        }

        return $headers;
    }

    /**
     * Gets the $_SERVER values
     *
     * @return array
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Gets the $_GET values
     *
     * @return array
     */
    public function getGet()
    {
        return $this->get;
    }

    /**
     * Gets the $_POST values
     *
     * @return array
     */
    public function getPost()
    {
        if ($this->post) {
            return $this->post;
        }

        if (in_array($this->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $contentType = $this->get('CONTENT_TYPE');

            if (strpos($contentType, 'application/x-www-form-urlencoded') === 0) {
                parse_str(file_get_contents($this->input), $data);

                return $data ?: [];
            }

            if (strpos($contentType, 'application/json') === 0) {
                return json_decode(file_get_contents($this->input), true) ?: [];
            }
        }

        return [];
    }

    /**
     * Gets the $_COOKIE values
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookie;
    }

    /**
     * Gets the $_FILES values (and normalizes its structure)
     *
     * @param boolean $fixed
     *
     * @return array
     */
    public function getFiles($fixed = true)
    {
        if ($fixed && $this->files) {
            return self::fixArray($this->files);
        }

        return $this->files;
    }

    /**
     * Gets the raw input stream path
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Fix the $files order by converting from default wierd schema
     * [first][name][second][0], [first][error][second][0]...
     * to a more straightforward one.
     * [first][second][0][name], [first][second][0][error]...
     *
     * @param array $files An array with all files values
     *
     * @return array The files values fixed
     */
    private static function fixArray($files)
    {
        if (isset($files['name'], $files['tmp_name'], $files['size'], $files['type'], $files['error'])) {
            return self::moveToRight($files);
        }

        foreach ($files as &$file) {
            $file = self::fixArray($file);
        }

        return $files;
    }

    /**
     * Private function used by fixArray
     *
     * @param array $files An array with all files values
     *
     * @return array The files values fixed
     */
    private static function moveToRight($files)
    {
        if (!is_array($files['name'])) {
            return $files;
        }

        $results = array();

        foreach ($files['name'] as $index => $name) {
            $reordered = array(
                'name' => $files['name'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'size' => $files['size'][$index],
                'type' => $files['type'][$index],
                'error' => $files['error'][$index],
            );

            if (is_array($name)) {
                $reordered = self::moveToRight($reordered);
            }

            $results[$index] = $reordered;
        }

        return $results;
    }
}
