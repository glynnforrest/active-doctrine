<?php

namespace ActiveDoctrine\Tests\Fixtures\Events;

use ActiveDoctrine\Entity\Entity;

/**
 * Event
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Event extends Entity
{

    protected static $table = 'events';
    protected static $fields = [
        'id',
        'name',
        'start_time'
    ];
    protected static $types = [
        'start_time' => 'datetime'
    ];

}
