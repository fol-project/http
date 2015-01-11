<?php
/**
 * Fol\Http\ContainerTrait
 *
 * Trait with utilities to store and retrieve variables
 */
namespace Fol\Http;

trait ContainerTrait
{
    protected $items = [];

    /**
     * Constructor class. You can define the items directly
     *
     * @param array $items The items to store
     */
    public function __construct(array $items = null)
    {
        if ($items !== null) {
            $this->set($items);
        }
    }

    /**
     * ArrayAcces interface methods
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * Converts all items to a string
     */
    public function __toString()
    {
        $text = '';

        foreach ($this->items as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $text .= "$name: $value\n";
        }

        return $text;
    }

    /**
     * Counts all stored parameteres
     *
     * @return int The total number of parameters
     */
    public function length()
    {
        return count($this->items);
    }

    /**
     * Gets one or all parameters.
     *
     * $params->get() Returns all parameters
     * $params->get('name') Returns just this parameter
     *
     * @param null|string $name The parameter name
     *
     * @return string|array
     */
    public function get($name = null)
    {
        if ($name === null) {
            return $this->items;
        }

        if (isset($this->items[$name]) && $this->items[$name] !== '') {
            return $this->items[$name];
        }
    }

    /**
     * Sets one parameter or various new parameters
     *
     * @param string|array $name  The parameter name. You can define an array with name => value to insert various parameters
     * @param mixed        $value The parameter value.
     *
     * @return $this
     */
    public function set($name = null, $value = null)
    {
        if (is_array($name)) {
            $this->items = array_replace($this->items, $name);
        } elseif ($name) {
            $this->items[$name] = $value;
        } else {
            $this->items[] = $value;
        }

        return $this;
    }

    /**
     * Deletes one or all parameters
     *
     * $params->delete('name') Deletes one parameter
     * $params->delete() Deletes all parameter
     *
     * @param string $name The parameter name
     *
     * @return $this
     */
    public function delete($name = null)
    {
        if ($name === null) {
            $this->items = [];
        } else {
            unset($this->items[$name]);
        }

        return $this;
    }

    /**
     * Checks if a parameter exists
     *
     * @param string $name The parameter name
     *
     * @return boolean True if the parameter exists (even if it's null) or false if not
     */
    public function has($name)
    {
        return array_key_exists($name, $this->items);
    }
}
