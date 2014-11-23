<?php
/**
 * Fol\Http\ResponseHeaders
 *
 * Manage http response headers
 */
namespace Fol\Http;

class ResponseHeaders extends Headers
{
    public $cookies;

    /**
     * Constructor
     *
     * @param array $items The items to store
     */
    public function __construct(array $items = null)
    {
        parent::__construct($items);

        $this->cookies = new ResponseCookies();
    }

    /**
     * {@inheritDoc}
     */
    public function setFromString($string, $replace = true)
    {
        parent::setFromString($string, $replace);

        if (strpos($string, 'Set-Cookie:') === 0) {
            $this->cookies->setFromString($string);
        }
    }
}
