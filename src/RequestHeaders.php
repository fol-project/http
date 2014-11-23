<?php
/**
 * Fol\Http\Headers
 *
 * Manage http headers
 */
namespace Fol\Http;

class RequestHeaders extends Headers
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

        $this->cookies = new RequestCookies($items);
    }

    /**
     * {@inheritDoc}
     */
    public function setFromString($string, $replace = true)
    {
        parent::setFromString($string, $replace);

        if (strpos($string, 'Cookie:') === 0) {
            $this->cookies->setFromString($string);
        }
    }

    /**
     * Gets the authentication data
     *
     * @return array|false
     */
    public function getAuthentication()
    {
        if (!($authorization = $this->get('Authorization'))) {
            return false;
        }

        if (strpos($authorization, 'Basic') === 0) {
            $authorization = explode(':', base64_decode(substr($authorization, 6)), 2);

            return [
                'type' => 'Basic',
                'username' => $authorization[0],
                'password' => isset($authorization[1]) ? $authorization[1] : null
            ];
        } elseif (strpos($authorization, 'Digest') === 0) {
            $needed_parts = ['nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
            $data = ['type' => 'Digest'];

            preg_match_all('@('.implode('|', array_keys($needed_parts)).')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', substr($authorization, 7), $matches, PREG_SET_ORDER);

            foreach ($matches as $m) {
                $data[$m[1]] = $m[3] ? $m[3] : $m[4];
                unset($needed_parts[$m[1]]);
            }

            if (!$needed_parts) {
                return $data;
            }
        }

        return false;
    }
}
