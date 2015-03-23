<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Utils;
use Fol\Http\Url;

/**
 * Middleware to get the language from the path
 * Available configuration:
 *
 */
class Languages extends Middleware
{
    protected $languages = [];
    protected $redirect = false;

    /**
     * Constructor. Defines the middleware configuration:
     *
     * $languages array   List of available languages
     * $fromPath  boolean Get the language from the first directory
     * $redirect  boolean Redirect if the language is not included in the path ($fromPath must be true)
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (isset($config['languages'])) {
            $this->languages = (array) $config['languages'];
        }

        if (isset($config['redirect'])) {
            $this->redirect = (boolean) $config['redirect'];
        }

        if (!empty($config['fromPath'])) {
            $this->push([$this, 'getFromPath']);
        }

        $this->unshift([$this, 'getFromHeaders']);
    }

    /**
     * Detect the language using the Content-Language header and set the Content-Language in the response
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     */
    public function getFromHeaders(Request $request, Response $response, Middleware $stack)
    {
        $request->attributes['LANGUAGE'] = $this->getPreferredLanguage($request);

        $stack->next();

        if (!$response->headers->has('Content-Language')) {
            $response->headers->set('Content-Language', $request->attributes['language']);
        }
    }

    /**
     * Get the preferred language using the Accept-Language header
     *
     * @param Request $request
     *
     * @return null|string
     */
    protected function getPreferredLanguage(Request $request)
    {
        $languages = array_keys(Utils::parseHeader($request->headers->get('Accept-Language')));

        if (empty($this->languages)) {
            return isset($languages[0]) ? Utils::getLanguage($languages[0]) : null;
        }

        if (empty($languages)) {
            return isset($this->languages[0]) ? Utils::getLanguage($this->languages[0]) : null;
        }

        $common = array_values(array_intersect($languages, $this->languages));

        return Utils::getLanguage(isset($common[0]) ? $common[0] : $this->languages[0]);
    }

    /**
     * Get the language from the path
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     */
    public function getFromPath(Request $request, Response $response, Middleware $stack)
    {
        $baseUrl = $request->attributes['BASE_URL'];

        if (!($baseUrl instanceof Url)) {
            throw new \Exception("BaseUrl middleware is required to get the language from path");
        }

        $basePath = $baseUrl->getPath();
        $path = $request->url->getPath();

        if (strpos($path, $basePath) !== 0) {
            return $stack->next();
        }

        $relative = substr($path, strlen($basePath));

        if (preg_match('#^/('.implode('|', $this->languages).')($|/)#', $relative, $match)) {
            $request->attributes['LANGUAGE'] = $match[1];
            $baseUrl->setPath($baseUrl->getPath().'/'.$match[1]);

            return $stack->next();
        }

        if ($this->redirect && !empty($request->attributes['LANGUAGE'])) {
            $response->redirect($baseUrl->getUrl().'/'.$request->attributes['LANGUAGE'].$relative);

            return false;
        }

        $stack->next();
    }
}
