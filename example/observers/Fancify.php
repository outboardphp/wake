<?php

use Venue\Event;

/**
 * An example Venue Observer
 *
 * This is an example Observer/plugin, which will modify
 * previously called listeners. This example Observer enhances
 * the display of posts
 *
 * @author      Garrett Whitehorn
 * @package     Venue
 * @subpackage  VenueExample
 * @version     1.0
 */
class Fancify extends Venue\Observer
{
    public function onCreatePost(Event $event) {
        return str_replace('border:1px solid #EEE;',
            'border:1px solid #DADADA;background:#F1F1F1;font-family:Arial;font-size:15px;',
            $event->previousResult);
    }
}
