<?php

namespace ActiveDoctrine\Tests\Entity;

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
            'belongs_to', 'ActiveDoctrine\Tests\Entity\Book', 'id', 'books_id'
        ]
    ];

}
