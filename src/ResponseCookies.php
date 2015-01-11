<?php
/**
 * Fol\Http\Cookies
 *
 * Class to manage cookies response cookies
 */
namespace Fol\Http;

class ResponseCookies implements \ArrayAccess
{
    use ContainerTrait;

    /**
     * Applies a default configuration to the cookies
     *
     * @param array $config An array with some or all of these keys:
     *
     * path
     * domain
     * secure
     * httponly
     */
    public function applyDefaults(array $config)
    {
        $availableValues = ['path', 'domain', 'secure', 'httponly'];

        if (array_diff(array_keys($config), $availableValues)) {
            throw new \Exception("Only the following fields are available:".implode(',', $availableValues));
        }

        foreach ($this->items as &$cookie) {
            foreach ($config as $name => $value) {
                if (!isset($cookie[$name])) {
                    $cookie[$name] = $value;
                }
            }
        }
    }

    /**
     * Magic function to converts all cookies to a string
     */
    public function __toString()
    {
        $text = '';

        foreach (array_keys($this->items) as $name) {
            $text .= $this->getAsString($name)."\n";
        }

        return $text;
    }

    /**
     * Sets a new cookie
     *
     * @param string                   $name     The cookie name
     * @param string                   $value    The cookie value
     * @param integer|string|\Datetime $expires  The cookie expiration time. It can be a number or a DateTime instance
     * @param string                   $path     The cookie path
     * @param string                   $domain   The cookie domain
     * @param boolean                  $secure   If the cookie is secure, only will be send in secure connection (https)
     * @param boolean                  $httponly If is set true, the cookie only will be accessed via http, so javascript cannot access to it.
     */
    public function set($name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if ($expires instanceof \DateTime) {
            $expires = $expires->format('U');
        } elseif (is_string($expires)) {
            $expires = strtotime($expires);
        }

        $this->items[$name] = [
            'name' => $name,
            'value' => $value,
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
        ];
    }

    /**
     * Deletes a cookie
     *
     * @param string  $name     The cookie name
     * @param string  $path     The cookie path
     * @param string  $domain   The cookie domain
     * @param boolean $secure   If the cookie is secure, only will be send in secure connection (https)
     * @param boolean $httponly If is set true, the cookie only will be accessed via http, so javascript cannot access to it.
     */
    public function setDelete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $this->set($name, '', 1, $path, $domain, $secure, $httponly);
    }

    /**
     * Returns a cookie as string
     *
     * @param string $name The cookie name
     *
     * @return string|null
     */
    public function getAsString($name = null)
    {
        if ($name === null) {
            $cookies = [];

            foreach (array_keys($this->items) as $name) {
                $cookies[] = $this->getAsString($name);
            }

            return $cookies;
        }

        if (!($cookie = $this->get($name))) {
            return;
        }

        $string = urlencode($cookie['name']).'='.urlencode($cookie['value']).';';

        if ($cookie['expires'] < time()) {
            $string .= ' deleted;';
        }

        $string .= ' expires='.gmdate("D, d-M-Y H:i:s T", $cookie['expires']).';';

        if ($cookie['path']) {
            $string .= ' path='.$cookie['path'];
        }

        if ($cookie['domain']) {
            $string .= ' domain='.$cookie['domain'].';';
        }

        if ($cookie['secure']) {
            $string .= ' secure;';
        }

        if ($cookie['httponly']) {
            $string .= ' httponly;';
        }

        return $string;
    }

    /**
     * Adds a new cookie from a Set-Cookie header string
     *
     * @param string $string
     *
     * @return boolean
     */
    public function setFromString($string)
    {
        if (strpos($string, 'Set-Cookie:') !== 0) {
            return false;
        }

        $string = trim(substr($string, 11));
        $data = ['expires' => null, 'path' => null, 'domain' => null, 'secure' => null, 'httponly' => null];
        $pieces = array_filter(array_map('trim', explode(';', $string)));

        if (empty($pieces) || !strpos($pieces[0], '=')) {
            return false;
        }

        foreach ($pieces as $part) {
            $cookieParts = explode('=', $part, 2);
            $key = trim($cookieParts[0]);
            $value = isset($cookieParts[1]) ? trim($cookieParts[1], " \n\r\t\0\x0B\"") : true;

            if (empty($data['name'])) {
                $data['name'] = $key;
                $data['value'] = $value;
            } else {
                $data[strtolower($key)] = $value;
            }
        }

        $this->set($data['name'], $data['value'], $data['expires'], $data['path'], $data['domain'], $data['secure'], $data['httponly']);

        return true;
    }
}
