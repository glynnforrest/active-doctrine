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

    public function testEmptyTable()
    {
        $this->assertNull(Book::selectOne($this->getConn())->execute());
    }

}
