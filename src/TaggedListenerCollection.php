<?php

declare(strict_types=1);

namespace Venue;

use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\TypeReflection;

class TaggedListenerCollection
{
    /**
     * @param array $listeners Takes the form of: ['tagName' => ['EventClass' => [callable, ...], ...], ...]
     */
    public function __construct(private array $listeners = [])
    {
    }

    public function getListenersForTag(string $tag): iterable
    {
        return $this->listeners[$tag] ?? [];
    }

    /**
     * Adds a new listener to a given tag along with the event(s) it listens for.
     *
     * @param string $tag
     * @param callable $listener Must accept one typehinted parameter: an event object
     * @param string|array $events The event(s) it will listen for, if different from the parameter's typehint
     * @throws InvalidArgumentException if listener validation fails
     */
    public function add(string $tag, callable $listener, string|array $events = ''): static
    {
        if (!empty($events)) {
            if (is_string($events)) {
                $events = [$events];
            }
            foreach ($events as $eventName) {
                $this->listeners[$tag][$eventName][] = $listener;
            }
            return $this;
        }

        $matched = false;
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            $this->listeners[$tag][$paramType][] = $listener;
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
     * Remove all listeners for a certain tag and/or event name.
     */
    public function detachAll(string $tag, string $eventName = ''): void
    {
        if (empty($eventName)) {
            unset($this->listeners[$tag]);
        } else {
            unset($this->listeners[$tag][$eventName]);
        }
    }

    /**
     * Detaches a listener from an event.
     *
     * @param string $tag
     * @param callable $listener The exact listener that was originally attached
     * @param string $eventName The event it is listening for, if different from the parameter's typehint
     */
    public function detach(string $tag, callable $listener, string $eventName = '')
    {
        // Detach from manual event name
        $key = array_search($listener, $this->listeners[$tag][$eventName]);
        if ($key !== false) {
            unset($this->listeners[$tag][$eventName][$key]);
            // If there are no more listeners, remove the event
            if (empty($this->listeners[$tag][$eventName])) {
                $this->detachAll($tag, $eventName);
            }
            return;
        }

        // Detach from all event class names
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            $key = array_search($listener, $this->listeners[$tag][$paramType]); // todo: check param 2 for existence
            if ($key !== false) {
                unset($this->listeners[$tag][$paramType][$key]);
                // If there are no more listeners, remove the event
                if (empty($this->listeners[$tag][$paramType])) {
                    $this->detachAll($tag, $paramType);
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
