<?php
/**
 * Fol\Http\Headers
 *
 * Manage http headers
 */
namespace Fol\Http;

class Headers implements \ArrayAccess
{
    use ContainerTrait;

    /**
     * Normalize the name of the parameters.
     * self::normalize('CONTENT type') Returns "Content-Type"
     *
     * @param string $string The text to normalize
     *
     * @return string The normalized text
     */
    public static function normalize($string)
    {
        return str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $string))));
    }

    /**
     * Converts all headers to a string
     */
    public function __toString()
    {
        $text = '';

        foreach (array_keys($this->items) as $name) {
            foreach ($this->getAsString($name, false) as $header) {
                $text .= "$header\n";
            }
        }

        return $text;
    }

    /**
     * Stores new headers. You can define an array to store more than one at the same time
     *
     * @param string|array   $name    The header name
     * @param string|boolean $value   The header value
     * @param boolean        $replace True to replace a previous header with the same name
     */
    public function set($name, $value = true, $replace = true)
    {
        if (is_array($name)) {
            $replace = (bool) $value;

            foreach ($name as $n => $value) {
                $this->set($n, $value, $replace);
            }

            return;
        }

        $name = self::normalize($name);

        if ($replace || !isset($this->items[$name])) {
            $this->items[$name] = [$value];
        } else {
            $this->items[$name][] = $value;
        }
    }

    /**
     * Gets one or all parameters
     *
     * @param string  $name  The header name
     * @param boolean $first Set true to return just the value of the first header with this name. False to return an array with all values.
     *
     * @return null|string|array The header value or an array with all values
     */
    public function get($name = null, $first = true)
    {
        if (func_num_args() === 0) {
            return $this->items;
        }

        $name = self::normalize($name);

        if (isset($this->items[$name])) {
            return $first ? $this->items[$name][0] : $this->items[$name];
        }
    }

    /**
     * Gets one parameter as a getDateTime object
     * Useful for datetime values (Expires, Last-Modification, etc)
     *
     * @param string $name The header name
     *
     * @return null|\DateTime The value in a datetime object or false
     */
    public function getDateTime($name)
    {
        if ($this->has($name)) {
            return \DateTime::createFromFormat(DATE_RFC2822, $this->get($name), new \DateTimeZone('GMT'));
        }
    }

    /**
     * Define a header using a Datetime object and returns it
     *
     * @param string           $name     The header name
     * @param \DateTime|string $datetime The datetime object. You can define also an string so the Datetime object will be created
     *
     * @return \Datetime The datetime object
     */
    public function setDateTime($name, $datetime = null)
    {
        if (!($datetime instanceof \Datetime)) {
            $datetime = new \DateTime($datetime);
        }

        $datetime->setTimezone(new \DateTimeZone('GMT'));
        $this->set($name, $datetime->format('D, d M Y H:i:s').' GMT');

        return $datetime;
    }

    /**
     * Deletes one or all headers
     *
     * $headers->delete('content-type') Deletes one header
     * $headers->delete() Deletes all headers
     *
     * @param string $name The header name
     */
    public function delete($name = null)
    {
        if (func_num_args() === 0) {
            $this->items = array();
        } else {
            $name = self::normalize($name);

            unset($this->items[$name]);
        }
    }

    /**
     * Checks if a header exists
     *
     * @param string $name The header name
     *
     * @return boolean True if the header exists, false if not
     */
    public function has($name)
    {
        return array_key_exists(self::normalize($name), $this->items);
    }

    /**
     * Returns a header as string
     *
     * @param string  $name  The header name
     * @param boolean $first Set true to return just the value of the first header with this name. False to return an array with all values.
     *
     * @return array|string|null
     */
    public function getAsString($name = null, $first = true)
    {
        if ($name === null) {
            $headers = [];

            foreach (array_keys($this->items) as $name) {
                foreach ($this->getAsString($name, false) as $header) {
                    $headers[] = $header;
                }
            }

            return $headers;
        }

        if (!($value = $this->get($name, $first))) {
            return;
        }

        if ($first) {
            return "{$name}: {$value}";
        }

        $headers = [];

        foreach ($value as $value) {
            $headers[] = "{$name}: {$value}";
        }

        return $headers;
    }

    /**
     * Adds a new header from a header string
     *
     * @param string  $string
     * @param boolean $replace
     *
     * @return boolean
     */
    public function setFromString($string, $replace = true)
    {
        if (strpos($string, ':') === false) {
            return false;
        }

        $header = array_map('trim', explode(':', $string, 2));

        $this->set($header[0], $header[1], $replace);

        return true;
    }
}
