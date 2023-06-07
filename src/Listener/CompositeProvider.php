<?php

declare(strict_types=1);

namespace Venue\Listener;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * A Listener Provider composed of multiple other Listener Providers.
 * Passes calls to `getListenersForEvent()` to each contained provider sequentially.
 */
class CompositeProvider implements ListenerProviderInterface
{
    /** @var ListenerProviderInterface[] */
    protected array $providers;

    public function __construct(ListenerProviderInterface ...$providers)
    {
        $this->providers = $providers;
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->providers as $provider) {
            yield from $provider->getListenersForEvent($event);
        }
    }

    public function providers(): array
    {
        return $this->providers;
    }
}
