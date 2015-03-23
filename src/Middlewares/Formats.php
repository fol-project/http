<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Utils;
use Fol\Http\Url;

/**
 * Middleware to get the format mime-type
 * using the Content-Type header and the file extension
 */
class Formats extends Middleware
{
    protected $formats;
    protected $default = 'html';
    protected $redirect = false;

    /**
     * Constructor. Defines the middleware configuration:
     *
     * $formats       array   List of available formats
     * $default       array   Default format if it's not available
     * $fromExtension boolean Get the format from the file extension
     * $redirect      boolean Redirect if the format is not included in the extension ($fromExtension must be true)
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (isset($config['formats'])) {
            $this->formats = (array) $config['formats'];
        } else {
            $this->formats = array_keys(Utils::getFormats());
        }

        if (isset($config['default'])) {
            $this->default = $config['default'];
        }

        if (isset($config['redirect'])) {
            $this->redirect = (boolean) $config['redirect'];
        }

        if (!empty($config['fromExtension'])) {
            $this->push([$this, 'getFromExtension']);
        }

        $this->unshift([$this, 'getFromHeaders']);
    }

    /**
     * Detect the format using the Accept header and set the Content-Type in the response
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     */
    public function getFromHeaders(Request $request, Response $response, Middleware $stack)
    {
        $request->attributes['FORMAT'] = $this->getPreferredFormat($request);

        $stack->next();

        if (!$response->headers->has('Content-Type')) {
            $mimetype = Utils::formatToMimeType($request->attributes['FORMAT'] ?: $this->default);
            $response->headers->set('Content-Type', "{$mimetype}; charset=UTF-8");
        }
    }

    /**
     * Detect and return the format.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function getPreferredFormat(Request $request)
    {
        if (($format = $request->url->getExtension()) && $this->isValid($format)) {
            return $format;
        }

        foreach (array_keys(Utils::parseHeader($request->headers->get('Accept'))) as $mimetype) {
            if (($format = Utils::mimetypeToFormat($mimetype)) && $this->isValid($format)) {
                return $format;
            }
        }

        return $this->default;
    }

    /**
     * Detect the format using file extension
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     */
    public function getFromExtension(Request $request, Response $response, Middleware $stack)
    {
        $baseUrl = $request->attributes['BASE_URL'];

        if (!($baseUrl instanceof Url)) {
            throw new \Exception("BaseUrl middleware is required to get the format from path");
        }

        $format = $request->url->getExtension();

        if ($this->isValid($format)) {
            $request->attributes['FORMAT'] = $format;

            return $stack->next();
        }

        if (empty($format) && $this->redirect && !empty($request->attributes['FORMAT'])) {
            if ($request->url->getPath() === $baseUrl->getPath()) {
                $request->url->setPath($request->url->getPath().'/index');
            }

            $request->url->setExtension($request->attributes['FORMAT']);
            $response->redirect($request->url->getUrl());

            return false;
        }

        $stack->next();
    }

    /**
     * Check if a format is valid or not.
     *
     * @param string $format
     *
     * @return boolean
     */
    protected function isValid($format)
    {
        if (empty($format)) {
            return false;
        }

        return in_array($format, $this->formats);
    }
}
