<?php
/**
 * Fol\Http\Router\ErrorRoute.
 *
 * Class to manage an error route
 */

namespace Fol\Http\Router;

class ErrorRoute extends Route
{
    public $target;

    /**
     * Constructor.
     *
     * @param array $config One available configuration: target
     */
    public function __construct(array $config)
    {
        $this->target = $config['target'];
    }
}
