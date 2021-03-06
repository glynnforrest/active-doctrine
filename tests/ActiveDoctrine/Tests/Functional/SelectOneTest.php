<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;
use ActiveDoctrine\Selector\AbstractSelector;

/**
 * SelectOneTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectOneTest extends FunctionalTestCase
{

    public function setup()
    {
        $this->loadSchema('bookshop');
    }

    /**
     * Check that selecting one entity works with both methods.
     */
    public function selectorProvider()
    {
        return [
            [Book::selectOne($this->getConn())],
            [Book::select($this->getConn())->one()]
        ];
    }

    /**
     * @dataProvider selectorProvider()
     */
    public function testEmptyTable($selector)
    {
        $this->assertNull($selector->execute());
    }

    /**
     * @dataProvider selectorProvider()
     */
    public function testSingleId($selector)
    {
        $this->loadData('bookshop');
        $book = $selector->where('id', '=', 3)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('3', $book->id);
    }

    public function testSelectSQLSingleId()
    {
        $this->loadData('bookshop');
        $conn = $this->getConn();
        $s = AbstractSelector::fromConnection($conn, 'books')->where('id', '=', 3);
        $sql = $s->getSQL();
        $params = $s->getParams();
        $book = Book::selectOneSQL($this->getConn(), $sql, $params);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('3', $book->id);
    }

    /**
     * @dataProvider selectorProvider()
     */
    public function testOneFromManyResults($selector)
    {
        $this->loadData('bookshop');
        $book = $selector->orderBy('id', 'DESC')
            ->limit(100)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame('50', $book->id);
    }

    public function testSelectPrimaryKey()
    {
        $this->loadData('bookshop');
        $book = Book::selectPrimaryKey($this->getConn(), 1);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Bookshop\Book', $book);
        $this->assertSame(1, (int) $book->id);
    }

    public function testNullRelationIsNotQueriedManyTimes()
    {
        $this->loadData('bookshop');
        $book = Book::selectPrimaryKey($this->getConn(), 50);
        $this->resetQueryCount();
        $this->assertNull($book->details);
        //make sure null has been saved and not queried for again
        $book->details;
        $book->details;
        $book->details;
        $this->assertSame(1, $this->getQueryCount());
    }
}
