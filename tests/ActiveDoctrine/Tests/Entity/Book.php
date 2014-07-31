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
        'author_id'
    ];
    protected static $relations = [
        'author' => ['has_one', 'ActiveDoctrine\Tests\Entity\Author', 'id', 'author_id'],
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
