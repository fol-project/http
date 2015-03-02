<?php
namespace Fol\Http\Middlewares;

use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\MiddlewareInterface;

/**
 * Middleware to get the client ip
 */
class IpDetector implements MiddlewareInterface
{
    protected $headers = [
        'Client-Ip',
        'X-Forwarded-For',
        'X-Forwarded',
        'X-Cluster-Client-Ip',
        'Forwarded-For',
        'Forwarded',
    ];

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
        $ips = $this->getIps($request);

        $request->attributes->set('IP', isset($ips[0]) ? $ips[0] : null);

        $stack->next();
    }

    /**
     * Detect and return all ips found
     *
     * @param Request         $request
     * 
     * @return array
     */
    protected function getIps(Request $request)
    {
        $ips = [];

        foreach ($this->headers as $name) {
            if ($request->headers->has($name)) {
                foreach (array_map('trim', explode(',', $request->headers->get($name))) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $ips[] = $ip;
                    }
                }
            }
        }

        return $ips;
    }
}
