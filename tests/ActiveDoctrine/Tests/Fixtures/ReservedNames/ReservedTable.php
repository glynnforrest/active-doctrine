<?php

namespace ActiveDoctrine\Tests\Fixtures\ReservedNames;

use ActiveDoctrine\Entity\Entity;

/**
 * ReservedTable
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReservedTable extends Entity
{
    protected static $table = 'insert';
    protected static $fields = ['id', 'name'];
}
