<?php

declare(strict_types=1);

namespace Venue\Listener;

use Psr\EventDispatcher\ListenerProviderInterface;
use Venue\NamedEvent;

/**
 * Mapper from an event to the listeners that are applicable to that event.
 *
 * @see https://www.php-fig.org/psr/psr-14/
 */
class Provider implements ListenerProviderInterface
{
    public function __construct(protected Collection $listeners)
    {
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        // if this event is named, look for that first
        if ($event instanceof NamedEvent) {
            yield from $this->listeners->getForEvents($event->name());
        }

        // now find all listeners accepting objects that match this event class
        yield from $this->listeners->getForEvents($event::class);
        yield from $this->listeners->getForEvents(...array_values(class_parents($event)));
        yield from $this->listeners->getForEvents(...array_values(class_implements($event)));
    }

    public function listeners(): Collection
    {
        return $this->listeners;
    }
}
