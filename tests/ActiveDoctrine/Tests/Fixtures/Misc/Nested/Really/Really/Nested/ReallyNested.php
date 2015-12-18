<?php

namespace ActiveDoctrine\Tests\Fixtures\Misc\Nested\Really\Really\Nested;

use ActiveDoctrine\Entity\Entity;

/**
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReallyNested extends Entity
{
    protected static $table = 'really_nested';
    protected static $fields = [
        'something_else'
    ];
    protected static $types = [
        'something_else' => 'float'
    ];
}
