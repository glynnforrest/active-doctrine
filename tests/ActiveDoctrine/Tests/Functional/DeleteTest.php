<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;

/**
 * DeleteTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class DeleteTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('bookshop');
        $this->loadData('bookshop');
    }

    public function testDeleteCollection()
    {
        $books = Book::select($this->getConn())->execute();
        $this->assertSame(50, count($books));

        $books->delete();

        $no_books = Book::select($this->getConn())->execute();
        $this->assertSame(0, count($no_books));
    }
}
