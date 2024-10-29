<?php

declare(strict_types=1);

namespace Venue;

use Fig\EventDispatcher\TaggedProviderTrait;

class TaggedListenerProvider extends ListenerProvider
{
    use TaggedProviderTrait;

    public function __construct(
        public readonly TaggedListenerCollection $listeners,
        protected string $eventType = 'stdClass',
        protected string $tagMethod = 'tag'
    ) {}

    protected function eventType(): string
    {
        return $this->eventType;
    }

    protected function tagMethod(): string
    {
        return $this->tagMethod;
    }

    protected function getListenersForTag(string $tag): iterable
    {
        return $this->listeners->getListenersForTag($tag);
    }

    protected function getListenersForAllTags(): iterable
    {
        return $this->getListenersForTag('*');
    }
}
