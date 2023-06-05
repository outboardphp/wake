<?php

namespace Venue;

use Psr\EventDispatcher\EventDispatcherInterface;
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
 */
class Event implements StoppableEventInterface
{
    /** @var bool Indicates whether the event should be handled by further listeners */
    private $stopPropagation = false;

    /** @var Dispatcher|null The Dispatcher handling us */
    private $dispatcher = null;

    /** @var array Contains the "return" values of previously-called event listeners */
    private $previousResults = [];

    /**
     * Constructor method of Event.
     *
     * All of these properties' usage details are left up to the event listener,
     * so see your event listener to know what to pass here.
     *
     * @param array $data An array of data to be used by the event's listener (optional)
     * @param object|string|null $context The object or class name that dispatched this event (optional)
     */
    public function __construct(private array $data = [], private readonly object|string|null $context = null)
    {}

    /**
     * Get the object or class name that dispatched this event
     */
    public function context(): object|string|null
    {
        return $this->context;
    }

    /**
     * Get/set event data from an internal array
     *
     * @return mixed the value set or retrieved
     */
    public function data(int|string $key, mixed $val = null): mixed
    {
        if ($val === null) {
            return $this->data[$key];
        }
        $this->data[$key] = $val;
        return $val;
    }

    /**
     * Get/set the dispatcher handling this event
     *
     * @return Dispatcher|null|void
     */
    public function dispatcher(?Dispatcher $dispatcher)
    {
        if ($dispatcher === null) {
            return $this->dispatcher;
        }
        if (isset($this->dispatcher)) {
            throw new \LogicException('Event already connected to a dispatcher');
        }
        if (!($dispatcher instanceof EventDispatcherInterface)) {
            throw new \InvalidArgumentException(
                'Invalid dispatcher; must be an object implementing Psr\EventDispatcher\EventDispatcherInterface'
            );
        }

        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }

    /**
     * Get "return" value from most recently encountered listener that did so, or set a new one
     *
     * @return mixed the value set or retrieved
     */
    public function return(mixed $val = null): mixed
    {
        if ($val === null) {
            return end($this->previousResults);
        }
        $this->previousResults[] = $val;
        return $val;
    }

    /**
     * Get array of all values "returned" from listeners
     */
    public function returnAll(): array
    {
        return $this->previousResults;
    }

    /**
     * Determine whether the dispatcher should continue sending this event to further listeners
     *
     * @return bool whether propagation was stopped
     */
    public function stopPropagation(bool $stop = true): bool
    {
        $this->stopPropagation = $stop;
        return $stop;
    }
}
