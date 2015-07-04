<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\Bookshop\Author;

/**
 * SelectEagerLoadTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectEagerLoadTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('bookshop');
    }

    public function testSelectWithEager()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $books = Book::select($this->getConn())->with('author')->execute();
        foreach ($books as $book) {
            //ensure the related entities are actually loaded
            $book->author;
        }

        $this->assertSame('Thomas Hardy', $books[0]->author->name);
        //only two queries should have run
        $this->assertSame(2, $this->getQueryCount());
    }
}
