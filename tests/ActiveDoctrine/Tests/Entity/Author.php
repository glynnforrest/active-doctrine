<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\Entity;

/**
 * Author
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Author extends Entity
{

    protected static $table = 'authors';
    protected static $fields = [
        'id',
        'name',
    ];

}
