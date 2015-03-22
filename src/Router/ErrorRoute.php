<?php
namespace Fol\Http\Router;

/**
 * Class to manage an error route
 */
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
