<?php

namespace ActiveDoctrine\Tests\Fixtures\Repository;

use ActiveDoctrine\Repository\AbstractRepository;

/**
 * BookRepository
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BookRepository extends AbstractRepository
{
    protected $entity_class = 'ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book';
}
