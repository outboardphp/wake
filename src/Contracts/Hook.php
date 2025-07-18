<?php

namespace Outboard\Wake\Contracts;

/**
 * Hooks support bidirectional communication, as they keep track of values returned by listeners.
 * So each successive listener can access the returned value of the previous one, and the code
 * firing the event can get data back after all listeners are called.
 */
interface Hook
{
    /**
     * Add returned value from a listener
     *
     * @param mixed $val the value to add, defaults to null
     * @return mixed the result that was added
     */
    public function addResult($val = null);

    /**
     * Get the last value returned from listeners
     */
    public function getResult(): mixed;

    /**
     * Get all values returned from listeners
     *
     * @return iterable<mixed> containing all results
     */
    public function getAllResults(): iterable;
}
