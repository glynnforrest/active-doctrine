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

    protected static $table = 'writers';
    protected static $fields = [
        'id',
        'forename',
        'surname',
        'createdAt',
        'updatedAt',
        'anotherCreate',
        'anotherUpdate',
    ];
    protected static $types = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'anotherCreate' => 'datetime',
        'anotherUpdate' => 'datetime',
    ];
    protected static $insert_timestamps = [
        'createdAt',
        'anotherCreate',
    ];
    protected static $update_timestamps = [
        'updatedAt',
        'anotherUpdate',
    ];
}
