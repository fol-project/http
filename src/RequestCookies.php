<?php
namespace Fol\Http;

use Fol\Bag;

/**
 * Class to store incoming cookies
 */
class RequestCookies extends Bag
{
    /**
     * Returns the cookies as a headers.
     *
     * @param string $name       The cookie name
     * @param string $headerName The cookie header name
     *
     * @return string|null
     */
    public function getAsString($name = null, $headerName = 'Cookie: ')
    {
        if ($name === null) {
            if (empty($this->items)) {
                return;
            }

            $header = $headerName;

            foreach ($this->items as $name => $value) {
                $header .= urlencode($name).'='.urlencode($value).';';
            }

            return $header;
        }

        if (!($value = $this->get($name))) {
            return;
        }

        return $headerName.urlencode($name).'='.urlencode($value).';';
    }

    /**
     * Adds new cookies from a header string.
     *
     * @param string $string     The cookie header value
     * @param string $headerName The cookie header name
     *
     * @return boolean
     */
    public function setFromString($string, $headerName = 'Cookie:')
    {
        if ($headerName) {
            if (strpos($string, $headerName) !== 0) {
                return false;
            }

            $string = substr($string, strlen($headerName));
        }

        foreach (array_filter(array_map('trim', explode(';', $string))) as $cookie) {
            $cookie = explode('=', $cookie, 2);

            $this->set(trim($cookie[0]), isset($cookie[1]) ? trim($cookie[1], " \n\r\t\0\x0B\"") : true);
        }

        return true;
    }
}
