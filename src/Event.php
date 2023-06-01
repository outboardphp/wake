<?php

namespace Venue;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Event class.
 *
 * Objects of this class will be passed to related listeners
 * along with their results. This class also allows
 * event handlers to easily share information with other event handlers.
 *
 * Extend this class if you want to impose some sort of structure on the data
 * contained in your specific event type. You could validate the $data array or
 * add custom properties.
 *
 * @version 1.0
 *
 * @property mixed $previousResult "return" value from the most recent listener
 */
class Event implements StoppableEventInterface
{
    /**
     * @api
     * @var mixed Who published this event
     */
    private $caller;

    /**
     * @api
     * @var bool Indicates whether further events should be handled
     */
    private $stopPropagation = false;

    /**
     * @api
     * @var Dispatcher|null An instance of the main Dispatcher class
     */
    private $dispatcher = null;

    /**
     * @api
     * @var array Contains the "return" values of previously-called event listeners
     */
    private $previousResults = [];

    /** @var array Contains the event's data */
    private $data;

    /**
     * Constructor method of Event.
     *
     * All of these properties' usage details are left up to the event listener,
     * so see your event listener to know what to pass here.
     *
     * @param array $data An array of values to be used by the event's listener (optional)
     * @param mixed $caller The calling object or class name (optional)
     *
     * @version 1.0
     */
    public function __construct($data = [], $caller = null)
    {
        $this->data = $data;
        $this->caller = $caller;
    }

    public function __get(string $name)
    {
        switch ($name) {
            case 'previousResult':
                return end($this->previousResults);
            
            case 'caller':
            case 'stopPropagation':
            case 'dispatcher':
            case 'previousResults':
                return $this->$name;

            default:
                return $this->data[$name];
        }
    }

    public function __set(string $name, $val)
    {
        switch ($name) {
            case 'stopPropagation':
                $this->stopPropagation = (bool) $val;
                break;

            case 'dispatcher':
                if ($val instanceof Observable || $val === null) {
                    $this->dispatcher = $val;
                }
                break;

            default:
                $this->data[$name] = $val;
        }
    }

    public function isPropagationStopped() : bool
    {
        return $this->stopPropagation;
    }

    public function return($val): void
    {
        $this->previousResults[] = $val;
    }
}
