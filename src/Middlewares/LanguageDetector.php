<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\MiddlewareInterface;
use Fol\Http\Utils;

/**
 * Middleware to get the preferred language
 * using the Accept-Language header.
 */
class LanguageDetector implements MiddlewareInterface
{
    protected $availableLanguages;

    /**
     * Constructor. Defines de available languages.
     *
     * @param array $availableLanguages
     */
    public function __construct(array $availableLanguages = null)
    {
        $this->availableLanguages = $availableLanguages ?: array();
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
        $language = $this->getPreferredLanguage($request);

        $request->attributes->set('LANGUAGE', $language);

        $stack->next();

        if (!$response->headers->has('Content-Language')) {
            $response->headers->set('Content-Language', $language);
        }
    }

    /**
     * Run as a middleware.
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    protected function getPreferredLanguage(Request $request)
    {
        $languages = array_keys(Utils::parseHeader($request->headers->get('Accept-Language')));

        if (empty($this->availableLanguages)) {
            return isset($languages[0]) ? Utils::getLanguage($languages[0]) : null;
        }

        if (empty($languages)) {
            return isset($this->availableLanguages[0]) ? Utils::getLanguage($this->availableLanguages[0]) : null;
        }

        $common = array_values(array_intersect($languages, $this->availableLanguages));

        return Utils::getLanguage(isset($common[0]) ? $common[0] : $this->availableLanguages[0]);
    }
}
