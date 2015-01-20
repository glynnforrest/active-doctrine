<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Tests\Fixtures\Bookshop\BookDetails;

/**
 * SetAndUnsetRelationsTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SetAndUnsetRelationsTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('bookshop');
        $this->loadData('bookshop');
    }

    public function testSetHasOne()
    {
        $book = Book::selectOne($this->getConn())->execute();
        $this->assertFalse($book->hasRelation('details'));

        $details = BookDetails::selectOne($this->getConn())->execute();
        $this->assertNotSame($book->id, $details->books_id);

        $book->details = $details;
        $this->assertSame($book->id, $details->books_id);
        $this->assertSame($details, $book->details);
        $this->assertTrue($book->hasRelation('details'));
    }

    public function testUnsetHasOne()
    {
        $book = Book::selectOne($this->getConn())->where('id', 2)->execute();
        $this->assertTrue($book->hasRelation('details'));
        $this->assertSame($book->id, $book->details->books_id);

        $details = $book->details;
        $book->details = 0;

        $this->assertFalse($book->hasRelation('details'));
        $this->assertSame(0, $details->books_id);
    }
}
