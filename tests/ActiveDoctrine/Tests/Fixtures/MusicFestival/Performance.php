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
        'id',
        'name',
        'start_time',
    ];
    protected static $types = [
        'start_time' => 'datetime',
    ];
}
