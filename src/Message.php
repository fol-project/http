<?php
/**
 * Fol\Http\Message
 *
 * Class to manage a http message
 */
namespace Fol\Http;

abstract class Message
{
    public $headers;

    protected $body;
    protected $bodyStream = false;
    protected $sendCallback;
    protected $prepareCallbacks = [];
    protected $functions = [];
    protected $services = [];


    /**
     * Register new custom constructors
     *
     * @param string   $name     The constructor name
     * @param \Closure $resolver A function that returns a Message instance
     */
    public static function registerConstructor($name, \Closure $resolver = null)
    {
        static::$constructors[$name] = $resolver;
    }

    /**
     * Register a new function
     *
     * @param string   $name     The function name
     * @param \Closure $resolver The function closure
     */
    public function registerFunction($name, \Closure $resolver = null)
    {
        $this->functions[$name] = $resolver;
    }

    /**
     * Register new services
     *
     * @param string   $name     The service name
     * @param \Closure $resolver A function that returns a service instance
     */
    public function register($name, \Closure $resolver = null)
    {
        $this->services[$name] = $resolver;
    }


    /**
     * Execute custom constructors
     * 
     * @throws \Exception if the constructor doesn't exist
     *
     * @return Message
     */
    public static function __callStatic($name, $arguments)
    {
        if (!empty(static::$constructors)) {
            return call_user_func_array(static::$constructors[$name], $arguments);
        }

        throw new \Exception("'$name' constructor is not defined");
    }


    /**
     * Magic function to execute custom functions
     *
     * @param string $name      The function name
     * @param array  $arguments The function arguments
     *
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (!isset($this->functions[$name])) {
            throw new \LogicException("The function '$name' does not exist");
        }

        return call_user_func_array($this->functions[$name], $arguments);
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
        if (!empty($this->services[$name])) {
            return $this->$name = call_user_func_array($this->services[$name], array_slice(func_get_args(), 1));
        }
    }


    /**
     * Sets the message body
     *
     * @param string|resource $body     The string or stream handler or stream filename
     * @param boolean         $isStream True to define the body as stream.
     */
    public function setBody($body, $isStream = false)
    {
        $this->bodyStream = is_resource($body) ?: $isStream;
        $this->body = $this->bodyStream ? $body : (string) $body;
    }

    /**
     * Gets the message body
     *
     * @return string|resource The body string or streaming resource
     */
    public function getBody()
    {
        if ($this->isStream()) {
            if (is_string($this->body)) {
                return $this->body = fopen($this->body, 'r+');
            }
        }

        return $this->body;
    }

    /**
     * Gets whether the body is stream or not
     *
     * @return boolean
     */
    public function isStream()
    {
        return $this->bodyStream;
    }

    /**
     * Write content in the body
     *
     * @param string $content
     * @param int    $length  Only used on streams
     *
     * @return int|null
     */
    public function write($content, $length = null)
    {
        if ($content === '') {
            return;
        }

        if ($this->isStream()) {
            return fwrite($this->getBody(), $content, $length);
        }

        $this->body .= (string) $content;
    }

    /**
     * Reads content from the body
     *
     * @return string
     */
    public function read()
    {
        $body = $this->getBody();

        if (is_string($body)) {
            return $body;
        }

        return stream_get_contents($body);
    }

    /**
     * Set the content callback
     */
    public function setSendCallback(callable $callback = null)
    {
        $this->sendCallback = $callback;
    }

    /**
     * Add a prepare callback
     */
    public function addPrepareCallback(callable $callback = null)
    {
        $this->prepareCallbacks[] = $callback;
    }

    /**
     * Executes the prepare callbacks
     */
    public function executePrepare(Request $request, Response $response)
    {
        foreach ($this->prepareCallbacks as $callback) {
            $callback($request, $response);
        }
    }
}
