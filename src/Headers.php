<?php
namespace Fol\Http;

use Fol\Bag;
use DateTime;
use DateTimeZone;

/**
 * Http headers bag
 */
class Headers extends Bag
{
    /**
     * Normalize the name of the parameters.
     * self::normalize('CONTENT type') Returns "Content-Type".
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
     * @see ArrayAccess
     */
    public function offsetExists($offset)
    {
        return parent::offsetExists(self::normalize($offset));
    }

    /**
     * @see ArrayAccess
     */
    public function offsetGet($offset)
    {
        return parent::offsetGet(self::normalize($offset));
    }

    /**
     * @see ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        return parent::offsetSet(self::normalize($offset), $value);
    }

    /**
     * @see ArrayAccess
     */
    public function offsetUnset($offset)
    {
        return parent::offsetUnset(self::normalize($offset));
    }

    /**
     * Converts all headers to a string.
     */
    public function __toString()
    {
        $text = '';

        foreach (array_keys($this->items) as $name) {
            foreach ($this->getAsString($name) as $header) {
                $text .= "$header\n";
            }
        }

        return $text;
    }

    /**
     * Gets one or all header.
     *
     * @param string $name The header name
     *
     * @return null|string|array
     */
    public function get($name = null)
    {
        if ($name === null) {
            return $this->items;
        }

        $value = parent::get($name);

        return is_array($value) ? implode(', ', $value) : $value;
    }

    /**
     * Gets one parameter as a getDateTime object
     * Useful for datetime values (Expires, Last-Modification, etc).
     *
     * @param string $name
     *
     * @return null|DateTime
     */
    public function getDateTime($name)
    {
        if ($this->has($name)) {
            return new DateTime($this->get($name), new DateTimeZone('GMT'));
        }
    }

    /**
     * Define a header using a Datetime object and returns it.
     *
     * @param string          $name
     * @param DateTime|string $datetime The datetime object. You can define also an string so the Datetime object will be created
     *
     * @return DateTime The datetime object
     */
    public function setDateTime($name, $datetime = null)
    {
        if (!($datetime instanceof Datetime)) {
            $datetime = new DateTime($datetime);
        }

        $datetime->setTimezone(new DateTimeZone('GMT'));
        $this->set($name, $datetime->format('D, d M Y H:i:s').' GMT');

        return $datetime;
    }

    /**
     * Returns a header as string.
     *
     * @param string $name The header name
     *
     * @return string[]
     */
    public function getAsString($name = null)
    {
        if ($name !== null) {
            $value = $this[$name];

            return $value ? ["{$name}: {$value}"] : [];
        }

        $headers = [];

        foreach ($this->items as $name => $value) {
            foreach ((array) $value as $v) {
                $headers[] = "{$name}: {$v}";
            }
        }

        return $headers;
    }

    /**
     * Adds a new header from a header string.
     *
     * @param string $string
     *
     * @return boolean
     */
    public function setFromString($string)
    {
        if (strpos($string, ':') === false) {
            return false;
        }

        $header = array_map('trim', explode(':', $string, 2));

        $this->set($header[0], $header[1]);

        return true;
    }
}
