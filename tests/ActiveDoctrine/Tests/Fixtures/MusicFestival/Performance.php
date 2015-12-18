<?php

namespace ActiveDoctrine\Tests\Fixtures\MusicFestival;

use ActiveDoctrine\Entity\Entity;

/**
 * Performance
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Performance extends Entity
{
    protected static $table = 'performances';
    protected static $fields = [
        'id' => 'integer',
        'name',
        'start_time' => 'datetime',
    ];
}
