<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Selector\AbstractSelector;
use ActiveDoctrine\Tests\Fixtures\Bookshop\Book;

/**
 * FieldMappingTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FieldMappingTest extends FunctionalTestCase
{
    public function setup()
    {
        $this->loadSchema('bookshop');
        $this->loadData('bookshop');
    }

    public function testSelectWithFieldMappings()
    {
        $query = 'SELECT b.id as i, b.name as n, b.description as d, b.authors_id as a FROM books b JOIN authors a ON b.authors_id = a.id';
        $books = Book::selectSQL($this->getConn(), $query, [], [
            'i' => 'id',
            'n' => 'name',
            'd' => 'description',
            'a' => 'authors_id',
        ]);

        $this->assertSame(50, count($books));
        $book = $books[0];
        $expectedValues = [
            'id' => '1',
            'name' => 'Book 1',
            'description' => 'The very first book',
            'authors_id' => '1',
        ];
        $this->assertSame($expectedValues, $book->getValues());
    }
}
