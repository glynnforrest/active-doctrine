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

    public function testSelectWithEagerTwoSteps()
    {
        $this->loadData('bookshop');
        $this->resetQueryCount();
        $authors = Author::select($this->getConn())
                 ->with('books', function($q) {
                     $q->with('details');
                 })->execute();

        //simulation of some application logic
        foreach ($authors as $author) {
            if (count($author->books) < 1 || !$author->books[0]->has('details')) {
                continue;
            }

            $author->books[0]->details->synposis;
        }

        $this->assertSame($authors[1]->books[0]->details->synopsis, 'Something something something');
        //only three queries: authors, books and details
        $this->assertSame(3, $this->getQueryCount());
    }
}
