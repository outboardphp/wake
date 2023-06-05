<?php

namespace Venue;

/**
 * Class for events that go by a name other than their class name.
 *
 * @see Event
 */
class NamedEvent extends Event
{
    /**
     * Constructor method of Event.
     *
     * All of these properties' usage details are left up to the event listener,
     * so see your event listener to know what to pass here.
     *
     * @param string $name The name of this event
     * @param array $data An array of data to be used by the event's listener (optional)
     * @param object|string|null $context The object or class name that dispatched this event (optional)
     */
    public function __construct(private readonly string $name, array $data = [], object|string|null $context = null)
    {
        parent::__construct($data, $context);
    }

    /**
     * Get event's name
     */
    public function name(): string
    {
        return $this->name;
    }
}
