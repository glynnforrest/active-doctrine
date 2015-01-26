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

    public function testDelete()
    {
        $book = Book::selectOne($this->getConn())
            ->where('id', 1)
            ->execute();
        $this->assertSame($book, $book->delete());

        $this->assertNull(Book::selectOne($this->getConn())->where('id', 1)->execute());
    }

    public function testDeleteCollection()
    {
        $books = Book::select($this->getConn())->execute();
        $this->assertSame(50, count($books));

        $books->delete();

        $no_books = Book::select($this->getConn())->execute();
        $this->assertSame(0, count($no_books));
    }

    public function testDeleteWithSelector()
    {
        Book::select($this->getConn())
            ->where('id', '<', 20)
            ->limit(10)
            ->execute()
            ->delete();

        $books_left = Book::select($this->getConn())->execute();
        $this->assertSame(40, count($books_left));
    }

}
