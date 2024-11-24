<?php

declare(strict_types=1);

namespace Venue;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Venue\Contracts\Hook;

/**
 * Dispatches events to applicable listeners via the registered provider.
 *
 * @see https://www.php-fig.org/psr/psr-14/
 */
class Dispatcher implements EventDispatcherInterface
{
    public function __construct(public readonly ListenerProviderInterface $provider) {}

    public function dispatch(object $event): object
    {
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            /** @var callable $listener */
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            try {
                if ($event instanceof Hook) {
                    $event->addResult($listener($event));
                    continue;
                }
                $listener($event);
            } catch (\Throwable $e) {
                // only dispatch a Throwable the first time around -
                // otherwise it could result in an endless loop in the
                // case where a throwable listener throws an exception
                if (!($event instanceof \Throwable)) {
                    $this->dispatch($e);
                }
                throw $e;
            }
        }

        return $event;
    }
}
