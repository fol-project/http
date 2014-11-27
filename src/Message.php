<?php
/**
 * Fol\Http\Message
 *
 * Class to manage a http message
 */
namespace Fol\Http;

use Psr\Http\Message\MessageInterface;

abstract class Message implements MessageInterface
{
    public $headers;

    protected $body;
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
     * {inheritDoc}
     */
    public function getProtocolVersion()
    {
        return "1.1";
    }

    /**
     * {inheritDoc}
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

    /**
     * {inheritDoc}
     */
    public function getHeaders()
    {
        return $this->headers->get();
    }

    /**
     * {inheritDoc}
     */
    public function hasHeader($header)
    {
        return $this->headers->has($header);
    }

    /**
     * {inheritDoc}
     */
    public function getHeader($header)
    {
        return implode(',', $this->getHeaderAsArray($header));
    }

    /**
     * {inheritDoc}
     */
    public function getHeaderAsArray($header)
    {
        return $this->headers->get($header, false, []);
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
