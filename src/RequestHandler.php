<?php
/**
 * Fol\Http\RequestHandler
 *
 * Class to manage the http request/response cycle
 */
namespace Fol\Http;

class RequestHandler
{
    protected $handlers = [];
    protected $services = [];
    protected $request;
    protected $baseUrl;
    protected $cookiesDefaultConfig = [];

    /**
     * Contructor. Set the basic configuration
     *
     * @param Request $request
     * @param string  $baseUrl
     */
    public function __construct(Request $request, $baseUrl = null)
    {
        $request->setHandler($this);
        $this->request = $request;

        if ($baseUrl === null) {
            $this->baseUrl = clone $request->url;
            $this->baseUrl->setPath('');
        } else {
            $this->baseUrl = new Url($baseUrl);
        }

        $this->setCookiesDefaultConfig([
            'domain' => $this->baseUrl->getHost(),
            'path' => $this->baseUrl->getPath(false),
            'secure' => ($this->baseUrl->getScheme() === 'https'),
            'httponly' => true,
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
     * Pushes a handler to the end of the stack.
     *
     * @param callable $handler The callback to execute
     */
    public function pushHandler(callable $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Removes the last handler and returns it
     *
     * @return callable|null
     */
    public function popHandler()
    {
        return array_pop($this->handlers);
    }

    /**
     * Prepare the response according with the current request
     *
     * @param Response $response
     */
    public function handle(Response $response)
    {
        $request = $this->getRequest();

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

        foreach ($this->handlers as $handler) {
            call_user_func($handler, $this, $response);
        }
    }
}
