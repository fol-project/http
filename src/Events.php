<?php
/**
 * Fol\Http\Events
 *
 * Class to manage the events of a http message
 */
namespace Fol\Http;

class Events
{
    protected $listeners = [];

    /**
     * Register a new event
     * 
     * @param string $event
     * @param callable $listener
     */
    public function on($event, callable $listener)
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;
    }

    /**
     * Remove one, various or all events
     * 
     * @param null|string $event If it's not defined, removes all events
     * @param null|callable $listener If it's not defined, removed all listeners
     */
    public function off($event = null, callable $listener = null)
    {
        if ($event === null) {
            $this->listeners = [];
        } elseif ($listener === null) {
            unset($this->listeners[$event]);
        } else {
            $index = array_search($listener, $this->listeners[$event], true);

            if ($index !== false) {
                unset($this->listeners[$event][$index]);
            }
        }
    }

    /**
     * Returns the listeners of one or all events
     * 
     * @param null|string $event If it's not defined, returns all listeners registered
     * 
     * @return array
     */
    public function get($event = null)
    {
        if ($event === null) {
            return $this->listeners;
        }

        return isset($this->listeners[$event]) ? $this->listeners[$event] : [];
    }

    /**
     * Emit an event
     * 
     * @param string $event
     * @param array $arguments
     */
    public function emit($event, array $arguments = array())
    {
        foreach ($this->get($event) as $listener) {
            call_user_func_array($listener, $arguments);
        }
    }
}
