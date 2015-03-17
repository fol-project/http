<?php
namespace Fol\Http;

use Fol\Bag;

/**
 * Class to manage response cookies
 */
class ResponseCookies extends Bag
{
    /**
     * Set the default config for cookies.
     *
     * @param array $config
     */
    public function setDefaultConfig(array $config)
    {
        foreach ($this->items as &$cookie) {
            foreach ($config as $name => $value) {
                if (!isset($cookie[$name])) {
                    $cookie[$name] = $value;
                }
            }
        }
    }

    /**
     * Magic function to converts all cookies to a string.
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
     * Sets a new cookie.
     *
     * @param string|array $name
     * @param string|array $value Value or an array with expires, path, domain, secure an httponly keys
     * 
     * @return $this
     */
    public function set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $n => $v) {
                $this->set($n, $v);
            }

            return $this;
        }

        if (!is_array($value)) {
            $value = ['value' => $value];
        } elseif (isset($value['expires'])) {
            if ($value['expires'] instanceof \DateTime) {
                $value['expires'] = $value['expires']->format('U');
            } elseif (is_string($value['expires'])) {
                $value['expires'] = strtotime($value['expires']);
            }
        }

        return parent::set($name, $value);
    }

    /**
     * Deletes a cookie.
     *
     * @param string  $name    The cookie name
     * @param array   $options Array with path, domain, secure an httponly keys
     */
    public function setDelete($name, array $options = array())
    {
        $options['value'] = '';
        $options['expires'] = 1;

        $this->set($name, $options);
    }

    /**
     * Returns a cookie as string.
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

        $cookie += [
            'expires' => null,
            'path' => null,
            'domain' => null,
            'secure' => null,
            'httponly' => null,
        ];

        $string = urlencode($name).'='.urlencode($cookie['value']).';';

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
     * Adds a new cookie from a Set-Cookie header string.
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
