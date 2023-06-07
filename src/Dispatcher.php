<?php

declare(strict_types=1);

namespace Venue;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Dispatches events to all applicable listeners.
 *
 * @see https://www.php-fig.org/psr/psr-14/
 */
class Dispatcher implements EventDispatcherInterface
{
    public function __construct(protected ListenerProviderInterface $provider)
    {
    }

    /**
     * @inheritDoc
     */
    public function dispatch(object $event): object
    {
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            /** @var callable $listener */
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            try {
                $listener($event);
            } catch (\Throwable $e) {
                // only dispatch a Throwable the first time around
                // otherwise it could result in an endless loop
                if (!($event instanceof \Throwable)) {
                    $this->dispatch($e);
                }
                throw $e;
            }
        }

        return $event;
    }

    public function getProvider(): ListenerProviderInterface
    {
        return $this->provider;
    }
}
