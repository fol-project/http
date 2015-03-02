<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Url;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;

/**
 * Middleware used to define a baseurl used by cookies, routers, etc
 */
class BaseUrl
{
    protected $url;

    /**
     * Set the base url
     * 
     * @param string|Url $url
     */
    public function __construct($url)
    {
        $this->url = ($url instanceof Url) ? $url : new Url($url);
    }

    /**
     * Run the middleware
     *
     * @param Request         $request
     * @param Response        $response
     * @param MiddlewareStack $stack
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, MiddlewareStack $stack)
    {
        $request->attributes->set('BASE_URL', $this->url);

        $stack->next();

        $response->cookies->setDefaultConfig([
            'domain' => $this->url->getHost(),
            'path' => $this->url->getPath(false),
            'secure' => ($this->url->getScheme() === 'https'),
            'httponly' => true,
        ]);
    }
}
