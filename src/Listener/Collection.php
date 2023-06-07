<?php

declare(strict_types=1);

namespace Venue\Listener;

use Technically\CallableReflection\CallableReflection;

class Collection
{
    /**
     * @param array $listeners Takes the form of: ['eventName' => [callable, ...], ...]
     */
    public function __construct(private array $listeners = [])
    {
    }

    /**
     * Get listeners for event class names specified.
     *
     * @param string ...$eventClassNames Event class names.
     *
     * @return iterable<callable> Listeners.
     */
    public function getForEvents(string ...$eventClassNames): iterable
    {
        foreach ($eventClassNames as $eventClassName) {
            if (isset($this->listeners[$eventClassName])) {
                yield from $this->listeners[$eventClassName];
            }
        }
    }


    /**
     * Attaches a new listener to an event.
     *
     * @param callable $listener Must accept one typehinted parameter: an event object
     * @param string $eventName The event it will listen for, if different from the parameter's typehint
     * @throws InvalidArgumentException if listener validation fails
     */
    public function attach(callable $listener, string $eventName = ''): static
    {
        if ($eventName) {
            $this->listeners[$eventName][] = $listener;
            return $this;
        }
        
        $foundOne = false;
        $this->getCallableParamTypesAndDo($listener, function ($paramType) use ($listener, $foundOne) {
            $this->listeners[$paramType][] = $listener;
            $foundOne = true;
        });
        if (!$foundOne) {
            throw new \InvalidArgumentException(
                'Listener callable must define one typehinted parameter to accept an event object'
            );
        }

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
