<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Entity\Book;

/**
 * SelectionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectionTest extends FunctionalTestCase
{

    public function setup()
    {
        $this->loadSchema('bookshop');
    }

    public function testSelectEmpty()
    {
        $books = Book::select($this->getConn())
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(0, count($books));
    }

    public function testSelectOneEmpty()
    {
        $this->assertNull(Book::selectOne($this->getConn())->execute());
    }

}
