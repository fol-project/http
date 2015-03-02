<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\MiddlewareInterface;
use Fol\Http\Utils;

/**
 * Middleware to get the format mime-type
 * using the Content-Type header.
 */
class FormatDetection implements MiddlewareInterface
{
    protected $availableFormats;
    protected $defaultFormat = 'html';

    /**
     * Constructor. Defines de available languages.
     *
     * @param array $availableFormats
     */
    public function __construct(array $availableFormats = null)
    {
        if ($availableFormats) {
            $this->availableFormats = $availableFormats;
        } else {
            $this->availableFormats = array_keys(Utils::getFormats());
        }
    }

    /**
     * Run the middleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, MiddlewareStack $stack)
    {
        $format = $this->getPreferredFormat($request);

        $request->attributes->set('FORMAT', $format);

        $stack->next();

        if (!$response->headers->has('Content-Type')) {
            $mimetype = Utils::formatToMimeType($format);
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

        return $this->defaultFormat;
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

        return in_array($format, $this->availableFormats);
    }
}
