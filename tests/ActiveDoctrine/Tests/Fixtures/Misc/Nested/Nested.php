<?php

namespace ActiveDoctrine\Tests\Fixtures\Misc\Nested;

use ActiveDoctrine\Entity\Entity;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Nested extends Entity
{
    protected static $table = 'nested';
    protected static $fields = [
        'something'
    ];
    protected static $types = [
        'something' => 'datetime'
    ];
}
