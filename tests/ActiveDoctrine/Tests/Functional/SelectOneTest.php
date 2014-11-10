<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book;

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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book', $book);
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
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book', $book);
        $this->assertSame('50', $book->id);
    }

}
