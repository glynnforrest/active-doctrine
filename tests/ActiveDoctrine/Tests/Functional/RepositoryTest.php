<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\BookRepository;

/**
 * RepositoryTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class RepositoryTest extends FunctionalTestCase
{
    protected $repo;

    public function setup()
    {
        $this->repo = new BookRepository($this->getConn());
        $this->loadSchema('bookshop');
    }

    public function testEmptyTable()
    {
        $books = $this->repo->findAll();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(0, count($books));
    }

    public function testFindAll()
    {
        $this->loadData('bookshop');
        $books = $this->repo->findAll();

        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(50, count($books));
        $book = $books[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 1', $book->name);
        $this->assertSame('The very first book', $book->description);
        $this->assertSame('1', $book->authors_id);
    }

    public function testFindBy()
    {
        $this->loadData('bookshop');
        $books = $this->repo->findBy(['id' => 3]);

        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(1, count($books));
        $book = $books[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 3', $book->name);
        $this->assertSame('Book 3 description', $book->description);
        $this->assertSame('3', $book->authors_id);
    }

    public function testFindOneBy()
    {
        $this->loadData('bookshop');
        $book = $this->repo->findOneBy(['id' => 3]);

        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 3', $book->name);
        $this->assertSame('Book 3 description', $book->description);
        $this->assertSame('3', $book->authors_id);
    }

    public function testFind()
    {
        $this->loadData('bookshop');
        $book = $this->repo->find(3);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 3', $book->name);

        $book = $this->repo->find(9);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('Book 9', $book->name);

        $this->assertNull($this->repo->find(1000));
    }
}
