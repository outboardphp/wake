<?php

use Venue\Event;

/**
 * An example Venue Observer
 *
 * This is an example Observer/plugin, which will override
 * previously called Observers. This example Observer enhances
 * the group and date formatting
 *
 * @author      Garrett Whitehorn
 * @package     Venue
 * @subpackage  VenueExample
 * @version     1.0
 */
class BetterFormatter extends Venue\Observer
{
    public function onFormatGroup(Event $event) {
        $groupName = strtolower($event->data);

        switch ($groupName):
            case 'admin':
            case 'administrator':
                $groupName = '<span style="color:#F00;">Administrator</span>';
                break;

            case 'mod':
            case 'moderator':
                $groupName = '<span style="color:#00A;">Moderator</span>';
                break;
        endswitch;

        return $groupName;
    }

    public function onFormatDate(Event $event) {
        return date('F j, Y h:i:s A T', $event->data);
    }
}
