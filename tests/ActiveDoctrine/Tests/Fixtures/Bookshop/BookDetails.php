<?php

namespace ActiveDoctrine\Tests\Fixtures\Bookshop;

use ActiveDoctrine\Entity\Entity;

/**
 * BookDetails
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BookDetails extends Entity
{

    protected static $table = 'book_details';
    protected static $fields = [
        'id',
        'books_id',
        'synopsis',
        'pages',
        'chapters'
    ];
    protected static $relations = [
        'book' => [
            'belongs_to', 'ActiveDoctrine\Tests\Fixtures\Bookshop\Book', 'id', 'books_id'
        ]
    ];
    protected static $blacklist = [
        'books_id'
    ];
}
