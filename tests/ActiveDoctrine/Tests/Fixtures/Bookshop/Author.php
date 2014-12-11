<?php

namespace ActiveDoctrine\Tests\Fixtures\Bookshop;

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
    protected static $relations = [
        'books' => ['has_many', 'ActiveDoctrine\Tests\Fixtures\Bookshop\Book', 'authors_id', 'id']
    ];

}
