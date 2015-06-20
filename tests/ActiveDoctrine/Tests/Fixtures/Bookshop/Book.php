<?php

namespace ActiveDoctrine\Tests\Fixtures\Bookshop;

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
        'author' => ['belongs_to', 'ActiveDoctrine\Tests\Fixtures\Bookshop\Author', 'id', 'authors_id'],
        'details' => ['has_one', 'ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails', 'books_id', 'id'],
        //invalid relation for the sake of testing errors
        'invalid' => 'foo'
    ];
    protected static $blacklist = [
        'id',
        'authors_id'
    ];
}
