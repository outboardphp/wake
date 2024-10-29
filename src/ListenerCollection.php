<?php

declare(strict_types=1);

namespace Venue;

use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\TypeReflection;

class ListenerCollection
{
    /**
     * @param array $listeners Takes the form of: ['eventName' => [callable, ...], ...]
     */
    public function __construct(private array $listeners = []) {}

    /**
     * Get listeners for event names specified.
     *
     * @param string ...$eventNames event class names
     * @return iterable<callable> listeners
     */
    public function getForEvents(string ...$eventNames): iterable
    {
        foreach ($eventNames as $eventName) {
            if (isset($this->listeners[$eventName])) {
                yield from $this->listeners[$eventName];
            }
        }
    }

    /**
     * Adds a new listener along with the event(s) it listens for.
     *
     * @param callable $listener Must accept one typehinted parameter: an event object
     * @param string|array $eventName The event(s) it will listen for, if different from the parameter's typehint
     * @throws InvalidArgumentException if listener validation fails
     */
    public function add(callable $listener, string|array $events = ''): static
    {
        if (!empty($events)) {
            if (is_string($events)) {
                $events = [$events];
            }
            foreach ($events as $eventName) {
                $this->listeners[$eventName][] = $listener;
            }
            return $this;
        }
        
        $matched = false;
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            $this->listeners[$paramType][] = $listener;
            $matched = true;
        }
        if (!$matched) {
            throw new \InvalidArgumentException(
                'Listener callable must define one typehinted parameter that accepts an event object'
            );
        }

        return $this;
    }

    /**
     * Remove all listeners for a certain event name.
     */
    public function detachAll(string $eventName): void
    {
        unset($this->listeners[$eventName]);
    }

    /**
     * Detaches a listener from an event.
     *
     * @param callable $listener The exact listener that was originally attached
     * @param string $eventName The event it is listening for, if different from the parameter's typehint
     */
    public function detach(callable $listener, string $eventName = '')
    {
        // Detach from manual event name
        $key = array_search($listener, $this->listeners[$eventName]);
        if ($key !== false) {
            unset($this->listeners[$eventName][$key]);
            // If there are no more listeners, remove the event
            if (empty($this->listeners[$eventName])) {
                $this->detachAll($eventName);
            }
            return;
        }

        // Detach from all event class names
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            $key = array_search($listener, $this->listeners[$paramType]); // todo: check param 2 for existence
            if ($key !== false) {
                unset($this->listeners[$paramType][$key]);
                // If there are no more listeners, remove the event
                if (empty($this->listeners[$paramType])) {
                    $this->detachAll($paramType);
                }
            }
        }
    }

    protected function getCallableParamTypes(callable $inspect): iterable
    {
        $reflection = CallableReflection::fromCallable($inspect);
        [$param] = $reflection->getParameters();
        foreach ($param->getTypes() as $paramType) {
            /** @var TypeReflection $paramType */
            if ($paramType->isObject()) {
                yield $paramType->getType();
            }
            if ($paramType->isClassRequirement()) {
                yield $paramType->getClassRequirement();
            }
        }
    }
}
