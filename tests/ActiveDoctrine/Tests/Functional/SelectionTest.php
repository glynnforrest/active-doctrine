<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book;

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

    public function testSelectCountEmpty()
    {
        $this->assertSame(0, Book::select($this->getConn())->count()->execute());
    }

    public function testSelect()
    {
        $this->loadData('bookshop');
        $books = Book::select($this->getConn())
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(50, count($books));
        $book = $books[0];
        $this->assertInstanceOf(Book::CLASS, $book);
        $this->assertSame('Book 1', $book->name);
        $this->assertSame('The very first book', $book->description);
        $this->assertSame('1', $book->authors_id);
    }

}
