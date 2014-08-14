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
        'synopsis',
        'pages',
        'chapters'
    ];
    protected static $relations = [
    ];

}
