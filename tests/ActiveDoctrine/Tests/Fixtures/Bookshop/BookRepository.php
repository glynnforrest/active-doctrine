<?php

namespace ActiveDoctrine\Tests\Fixtures\Bookshop;

use ActiveDoctrine\Repository\AbstractRepository;

/**
 * BookRepository
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BookRepository extends AbstractRepository
{
    protected $entity_class = 'ActiveDoctrine\Tests\Fixtures\Bookshop\Book';
}
