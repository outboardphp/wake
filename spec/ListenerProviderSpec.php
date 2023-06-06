<?php

namespace spec\Venue;

use PhpSpec\ObjectBehavior;
use Venue\ListenerProvider;

class ListenerProviderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ListenerProvider::class);
    }
}
