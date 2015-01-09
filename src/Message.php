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
    public $events;

    protected $protocol = '1.1';
    protected $body;
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
     * {inheritDoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * {@inheritDoc}
     */
    public function setProtocolVersion($version)
    {
        $this->protocol = $version;
    }

    /**
     * Returns the message body
     *
     * @return Body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the message body
     *
     * @param Body $body
     */
    public function setBody(Body $body)
    {
        $this->body = $body;
    }
}
