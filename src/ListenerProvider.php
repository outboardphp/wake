<?php

declare(strict_types=1);

namespace Venue;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Mapper from an event to the listeners that are applicable to that event.
 *
 * @see https://www.php-fig.org/psr/psr-14/
 */
class ListenerProvider implements ListenerProviderInterface
{
    public function __construct(public readonly ListenerCollection $listeners) {}

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        // now find all listeners accepting objects that match this event class
        yield from $this->listeners->getForEvents($event::class);
        yield from $this->listeners->getForEvents(...array_values(class_parents($event)));
        yield from $this->listeners->getForEvents(...array_values(class_implements($event)));
    }
}
