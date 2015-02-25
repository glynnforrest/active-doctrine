<?php

namespace ActiveDoctrine\Tests\Fixtures\Articles;

use ActiveDoctrine\Entity\Entity;
use ActiveDoctrine\Entity\Traits\TimestampTrait;

/**
 * Writer
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Writer extends Entity
{
    use TimestampTrait;

    protected static $insert_timestamps = [
        'createdAt',
        'anotherCreate',
    ];
    protected static $update_timestamps = [
        'updatedAt',
        'anotherUpdate',
    ];
}
