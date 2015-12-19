<?php

namespace ActiveDoctrine\Tests\Fixtures\Misc;

use ActiveDoctrine\Entity\Entity;

/**
 * Entity with no types to check default type behaviour.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class NoTypes extends Entity
{
    protected static $table = 'no_types';
    protected static $fields = [
        'id',
        'name'
    ];
}
