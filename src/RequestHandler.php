<?php
/**
 * Fol\Http\RequestHandler
 *
 * Class to manage the http request/response cycle
 */
namespace Fol\Http;

class RequestHandler
{
	protected $listeners = [];
    protected $services = [];
    protected $requests = [];
    protected $baseUrl;
    protected $cookiesDefaultConfig = [];


    /**
     * Contructor. Set the basic configuration
     * 
     * @param Request $request
     * @param string  $baseUrl
     */
    public function __construct(Request $request, $baseUrl = '')
    {
        $request->setHandler($this);
        $this->request = $request;

        $this->baseUrl = new Url($baseUrl);

        $this->setCookiesDefaultConfig([
            'domain' => $this->baseUrl->getHost(),
            'path' => $this->baseUrl->getPath(false),
            'secure' => ($this->baseUrl->getScheme() === 'https'),
            'httponly' => true
        ]);
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
            return $this->$name = call_user_func($this->services[$name], $this);
        }
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
     * Returns the base url
     * 
     * @return Url
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set the default cookies configuration
     * 
     * @param array $config
     */
    public function setCookiesDefaultConfig(array $config)
    {
        $this->cookiesDefaultConfig = array_replace($this->cookiesDefaultConfig, $config);
    }


    /**
     * Get the default cookies configuration
     * 
     * @param array
     */
    public function getCookiesDefaultConfig()
    {
        return $this->cookiesDefaultConfig;
    }

    /**
     * Set the request
     * 
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

	/**
     * Register a new event
     * 
     * @param string $event
     * @param callable $listener
     */
    public function on($event, callable $listener)
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;
    }

    /**
     * Remove one, various or all events
     * 
     * @param null|string $event If it's not defined, removes all events
     * @param null|callable $listener If it's not defined, removed all listeners
     */
    public function off($event = null, callable $listener = null)
    {
        if ($event === null) {
            $this->listeners = [];
        } elseif ($listener === null) {
            unset($this->listeners[$event]);
        } else {
            $index = array_search($listener, $this->listeners[$event], true);

            if ($index !== false) {
                unset($this->listeners[$event][$index]);
            }
        }
    }

    /**
     * Emit an event
     * 
     * @param string $event
     * @param array $arguments
     */
    public function emit($event, array $arguments = array())
    {
    	if (empty($this->listeners[$event])) {
    		return;
    	}

        foreach ($this->listeners[$event] as $listener) {
            call_user_func_array($listener, $arguments);
        }
    }

    /**
     * Prepare the response according with the current request
     * 
     * @param Response $response
     */
    public function prepare(Response $response)
    {
        $request = $this->getRequest();

        if (!$request || !$response) {
            throw new \RuntimeException('Missing Request or Response before prepare');
        }

    	if (!$request->headers->has('Content-Type') && ($format = $request->getFormat())) {
            $response->setFormat($format);
        }

        if (!$request->headers->has('Content-Language') && ($language = $request->getLanguage())) {
            $response->setLanguage($language);
        }

        if ($request->headers->has('Transfer-Encoding')) {
            $response->headers->delete('Content-Length');
        }

        if (!$response->headers->has('Date')) {
            $response->headers->setDateTime('Date', new \DateTime());
        }

        if ($request->getMethod() === 'HEAD') {
            $response->setBody(new Body());
        }

        $response->cookies->applyDefaults($this->getCookiesDefaultConfig());
    }


    /**
     * Sends the response to the client
     */
    public function send(Response $response)
    {
        $this->emit('beforeSend', [$this, $response]);

        $this->prepare($response);

        if (!headers_sent()) {
            header(sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()));

            foreach ($response->headers->getAsString() as $header) {
                header($header, false);
            }

            foreach ($response->cookies->get() as $cookie) {
                if (!setcookie($cookie['name'], $cookie['value'], $cookie['expires'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly'])) {
                    throw new \Exception('Error sending the cookie '.$cookie['name']);
                }
            }
        }

        $level = ob_get_level();

        while ($level > 0) {
            ob_end_flush();
            $level--;
        }

        $body = $response->getBody();
        $body->seek(0);

        while (!$body->eof()) {
            echo $body->read(1024);
            flush();
        }

        $body->close();

        $this->emit('afterSend', [$this, $response]);
    }
}
