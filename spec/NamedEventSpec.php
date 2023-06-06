<?php

namespace spec\Venue;

use PhpSpec\ObjectBehavior;
use Venue\NamedEvent;

class NamedEventSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(NamedEvent::class);
    }
}
