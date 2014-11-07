<?php

namespace ActiveDoctrine\Tests\Fixtures\Entities\Bookshop;

use ActiveDoctrine\Entity\Entity;

/**
 * Book
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class Book extends Entity
{

    protected static $table = 'books';
    protected static $fields = [
        'id',
        'name',
        'description',
        'authors_id'
    ];
    protected static $relations = [
        'author' => ['belongs_to', 'ActiveDoctrine\Tests\Entity\Author', 'id', 'authors_id'],
    ];

}
