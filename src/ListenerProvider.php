<?php

namespace Venue;

use Psr\EventDispatcher\ListenerProviderInterface;
use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\TypeReflection;

/**
 * Repository for listeners.
 */
class ListenerProvider implements ListenerProviderInterface
{
    /** @var array Contains registered event listeners */
    protected $listeners = [];

    public function getListenersForEvent(object $event): iterable
    {
        $allListeners = [];
        // if this event is named, try to match on that
        if (
            $event instanceof NamedEvent
            && ($eventName = $event->name())
            && isset($this->listeners[$eventName])
        ) {
            $allListeners += $this->listeners[$eventName];
        }

        // now find all listeners named after classes that match this event
        foreach ($this->listeners as $key => $listeners) {
            if (class_exists($key) && $event instanceof $key) {
                $allListeners += $listeners;
            }
        }

        return $allListeners;
    }

    /**
     * Attaches a new listener to an event.
     *
     * @param callable $listener Must accept one typehinted parameter: an event object
     * @param string $eventName The event it will listen for, if different from the parameter's typehint
     */
    public function attach(callable $listener, string $eventName = ''): static
    {
        if ($eventName) {
            $this->listeners[$eventName][] = $listener;
            return $this;
        }
        
        $this->getCallableParamTypesAndDo($listener, function ($paramType) use ($listener) {
            $this->listeners[$paramType][] = $listener;
        });
        return $this;
    }

    /**
     * Remove all listeners from a certain event name.
     */
    public function detachAll(string $eventName): void
    {
        unset($this->listeners[$eventName]);
    }

    /**
     * Detaches a listener from an event.
     *
     * @param callable $listener The exact listener that was originally attached
     * @param string $eventName The event it is listening for
     */
    public function detach(callable $listener, string $eventName)
    {
        // Search by manual event name
        $key = array_search($listener, $this->listeners[$eventName]);
        if ($key !== false) {
            unset($this->listeners[$eventName][$key]);
            // If there are no more listeners, remove the event
            if (empty($this->listeners[$eventName])) {
                $this->detachAll($eventName);
            }
        }

        // Search by event classname
        $this->getCallableParamTypesAndDo($listener, function ($paramType) use ($listener) {
            $key = array_search($listener, $this->listeners[$paramType]);
            if ($key !== false) {
                unset($this->listeners[$paramType][$key]);
                // If there are no more listeners, remove the event
                if (empty($this->listeners[$paramType])) {
                    $this->detachAll($paramType);
                }
            }
        });
    }

    protected function getCallableParamTypesAndDo(callable $inspect, callable $execute)
    {
        $reflection = CallableReflection::fromCallable($inspect);
        [$param] = $reflection->getParameters();
        foreach ($param->getTypes() as $paramType) {
            /** @var TypeReflection $paramType */
            if ($paramType->isClassName()) {
                $execute($paramType->getType());
            }
        }
    }
}
