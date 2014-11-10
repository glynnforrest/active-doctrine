<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book;

/**
 * SelectCountTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectCountTest extends FunctionalTestCase
{

    public function setup()
    {
        $this->loadSchema('bookshop');
    }

    public function testEmptyTable()
    {
        $this->assertSame(0, Book::select($this->getConn())->count()->execute());
    }

    public function testCount()
    {
        $this->loadData('bookshop');
        $count = Book::select($this->getConn())
            ->count()
            ->execute();
        $this->assertSame(50, $count);
    }

    public function testCountWhere()
    {
        $this->loadData('bookshop');
        $count = Book::select($this->getConn())
            ->count()
            ->where('id', '>', 30)
            ->execute();
        $this->assertSame(20, $count);
    }

    public function testCountLimit()
    {
        $this->loadData('bookshop');
        $count = Book::select($this->getConn())
            ->count()
            ->limit(20)
            ->execute();
        $this->assertSame(20, $count);
    }

    public function testCountLimitNotReached()
    {
        $this->loadData('bookshop');
        $count = Book::select($this->getConn())
            ->count()
            ->where('id', '>', 43)
            ->limit(20)
            ->execute();
        $this->assertSame(7, $count);
    }
}
