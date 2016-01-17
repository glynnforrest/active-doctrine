<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\Bookshop\Author;

/**
 * SelectByRelationTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectByRelationTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('bookshop');
    }

    public function testSelectWhereWithHasOneRelation()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $author = Author::selectOne($this->getConn())->execute();
        $books = Book::select($this->getConn())
               ->with('author')
               ->where('author', $author)
               ->execute();

        $this->assertEquals($author, $books[0]->author);
    }

    public function testSelectWhereWithHasOneRelationLongForm()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $author = Author::selectOne($this->getConn())->execute();
        $books = Book::select($this->getConn())
               ->with('author')
               ->where('author', '=', $author)
               ->execute();

        $this->assertEquals($author, $books[0]->author);
    }

    public function testSelectAndWhereWithHasOneRelation()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $author = Author::selectOne($this->getConn())->execute();
        $books = Book::select($this->getConn())
               ->with('author')
               ->where('id', '<', 30)
               ->andWhere('author', $author)
               ->execute();

        $this->assertEquals($author, $books[0]->author);
    }

    public function testSelectAndWhereWithHasOneRelationLongForm()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $author = Author::selectOne($this->getConn())->execute();
        $books = Book::select($this->getConn())
               ->with('author')
               ->where('id', '<', 30)
               ->andWhere('author', '=', $author)
               ->execute();

        $this->assertEquals($author, $books[0]->author);
    }

    public function testSelectOrWhereWithHasOneRelation()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $author = Author::selectOne($this->getConn())->execute();
        $books = Book::select($this->getConn())
               ->with('author')
               ->where('id', '<', 30)
               ->orWhere('author', $author)
               ->execute();

        $this->assertEquals($author, $books[0]->author);
    }

    public function testSelectOrWhereWithHasOneRelationLongForm()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $author = Author::selectOne($this->getConn())->execute();
        $books = Book::select($this->getConn())
               ->with('author')
               ->where('id', '<', 30)
               ->orWhere('author', '=', $author)
               ->execute();

        $this->assertEquals($author, $books[0]->author);
    }
}
