<?php

namespace spec\Venue;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ObserverSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beAnInstanceOf('spec\Venue\ObserverExample');
        $this->beConstructedWith(new \Venue\Mediator(new \Venue\Manager));
        $this->subscribe();
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf('Venue\Observer');
        $this->shouldBeAnInstanceOf('spec\Venue\ObserverExample');
    }

    function it_keeps_an_instance_of_mediator()
    {
        $this->mediator->shouldBeAnInstanceOf('Venue\Mediator');
    }

    function it_responds_to_all_events()
    {
        $this->mediator->publish(new \Venue\Event('random'))->shouldReturn('random');
    }
}

class ObserverExample extends \Venue\Observer
{
    public function subscribe($newhandler = null) {
        // This is just here for an example of explicitly-defined handlers
        $this->handlers = [
            'all' => [[$this, 'log'], \Venue\Manager::PRIORITY_URGENT, true],
            'one' => [[$this, 'handlerOne']],
        ];
        parent::subscribe($newhandler);
    }

    public function handlerOne(\Venue\Event $e)
    {
        return 'one';
    }

    public function onTwo(\Venue\Event $e)
    {
        return 'two';
    }

    public function log(\Venue\Event $e)
    {
        return $e->name;
    }
}
