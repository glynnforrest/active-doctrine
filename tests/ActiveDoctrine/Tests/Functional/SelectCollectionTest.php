<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Selector\AbstractSelector;

/**
 * SelectCollectionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectCollectionTest extends FunctionalTestCase
{

    public function setup()
    {
        $this->loadSchema('bookshop');
    }

    public function testEmptyTable()
    {
        $books = Book::select($this->getConn())
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(0, count($books));
    }

    public function testSimple()
    {
        $this->loadData('bookshop');
        $books = Book::select($this->getConn())
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(50, count($books));
        $book = $books[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 1', $book->name);
        $this->assertSame('The very first book', $book->description);
        $this->assertSame('1', $book->authors_id);
    }

    public function testSelectSQL()
    {
        $conn = $this->getConn();
        $this->loadData('bookshop');
        $sql = AbstractSelector::fromConnection($conn, 'books')
            ->getSQL();
        $books = Book::selectSQL($this->getConn(), $sql);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(50, count($books));
        $book = $books[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 1', $book->name);
        $this->assertSame('The very first book', $book->description);
        $this->assertSame('1', $book->authors_id);
    }

    public function testWhereEquals()
    {
        $this->loadData('bookshop');
        $books = Book::select($this->getConn())
            ->where('id', '=', 3)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(1, count($books));
        $book = $books[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 3', $book->name);
        $this->assertSame('Book 3 description', $book->description);
        $this->assertSame('3', $book->authors_id);
    }

    public function testWhereLessThan()
    {
        $this->loadData('bookshop');
        $books = Book::select($this->getConn())
            ->where('id', '<', 4)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(3, count($books));
        $this->assertSame(['Book 1', 'Book 2', 'Book 3'], $books->getColumn('name'));
    }

    public function testLimit()
    {
        $this->loadData('bookshop');
        $books = Book::select($this->getConn())
            ->limit(5)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(5, count($books));
        $this->assertSame(['1', '2', '3', '4', '5'], $books->getColumn('id'));
    }

    public function testLimitOffset()
    {
        $this->loadData('bookshop');
        $books = Book::select($this->getConn())
            ->limit(5)
            ->offset(7)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(5, count($books));
        $this->assertSame(['8', '9', '10', '11', '12'], $books->getColumn('id'));
    }

    public function testOrderByDesc()
    {
        $this->loadData('bookshop');
        $books = Book::select($this->getConn())
            ->orderBy('id', 'desc')
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(50, count($books));
        $book = $books[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('50', $book->get('id'));
    }

}
