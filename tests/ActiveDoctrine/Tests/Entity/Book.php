<?php

namespace ActiveDoctrine\Tests\Entity;

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
        'details' => ['has_one', 'ActiveDoctrine\Tests\Entity\BookDetails', 'books_id', 'id'],
        //invalid relation for the sake of testing errors
        'invalid' => 'fooo'
    ];

    public function setterName($name)
    {
        return strtoupper($name);
    }

    public function getterDescription()
    {
        return strtoupper($this->getRaw('description'));
    }

}
