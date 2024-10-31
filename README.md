Venue
======

Venue is a full-featured, [PSR-14](http://www.php-fig.org/psr/psr-14/) compliant event library for PHP, combining the Mediator and Observer patterns.
It includes an event dispatcher and a basic listener provider, as per the spec, and adds listener collections and
other variations on listener providers to meet special needs.

Currently in the middle of a major refactoring so documentation and tests are incomplete.

## Install
`composer require garrettw/venue`

## Basic Usage
```php
use Venue\ListenerCollection;
use Venue\ListenerProvider;
use Venue\Dispatcher;

$listeners = (new ListenerCollection())
    ->add(function (stdClass $event) {
        // This anonymous function is an event "listener". It listens for any
        // events being fired that match the typehint given on the parameter.
        // Here I'm having it handle any object that was fired as an event.
        // (The typehint to do that has to be 'stdClass' and not 'object',
        // because it has to match the classname of the object.)
        // You can also use union types to target more than one event class.
        // You would replace these comment lines with code that does
        // something in response to the event being fired.
    })->add(
        // You can daisy-chain add() calls because it returns the
        // collection object each time.
    );

// Now we have 1 listener in the collection, ready to use, so we initialize
// a ListenerProvider with our collection, and then give that to the Dispatcher.
$dispatcher = new Dispatcher(new ListenerProvider($listeners));
// and now our dispatcher is set up and ready as well.

// Let's create a very basic type of event object that the listener above will handle:
$event = new stdClass;
$event->data = 'some string';

// And now we'll fire it off and the registered listener(s) will handle it immediately.
$dispatcher->dispatch($event);
```

## Creating Your Own Event Classes
Venue provides a few interfaces and traits you may want to use when writing
event classes:

### Stopping Event Propagation
The most basic mechanism to allow an event to stop propagating to other listeners
involves implementing `Psr\EventDispatcher\StoppableEventInterface`, which
consists of a single method:
```php
public function isPropagationStopped(): bool;
```
You can implement that method yourself with any logic you want, or alternatively
you can just include this line in your class:
```php
    use \Venue\Traits\CanStopPropagation;
```
and control the behavior elsewhere within the class by simply setting
`$this->stopPropagation` to `true`.

#### External Control
On the other hand, if you want to also allow code outside of the event to control its
propagation, have your event class implement `Venue\Contracts\ForceStop` with the
following trait:
```php
    use \Venue\Traits\CanBeStopped;
```
This adds a method called `stopPropagation()` to your event class.

### Direction of Data Flow
With Venue, events largely represent unidirectional data flow; an event is
fired, and listeners receive it. The intent is for events to be immutable, but
since they can be any object and thus contain arbitrary behavior, they are not
guaranteed to be immutable as far as this library is aware.

In order to account for both unidirectional and bidirectional-flow use cases,
Venue includes a specific interface/trait combination to help you build
bidirectional event classes, which we call Hooks.
- Interface: `Venue\Contracts\Hook`
- Trait: `Venue\Traits\Hook`

The Venue Dispatcher is aware of the `Hook` interface, so when it encounters
one, it will store the return value of the listener(s) it calls back into the
event object. So after all listeners are called for a given Hook, and the
Dispatcher returns the event object, you can just call `getResult()` on the
event to retrieve the value returned by the last listener -- or if you want
an array of all listeners' return values, just call `getAllResults()` which
will return an array.
