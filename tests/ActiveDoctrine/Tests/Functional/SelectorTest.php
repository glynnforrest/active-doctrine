<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Selector\AbstractSelector;

/**
 * SelectorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectorTest extends FunctionalTestCase
{
    public function setup()
    {
        $this->loadSchema('bookshop');
    }

    protected function getSelector()
    {
        return AbstractSelector::fromConnection($this->getConn(), 'books');
    }

    public function testSimplePrepare()
    {
        $this->loadData('bookshop');
        $stmt = $this->getSelector()->prepare();
        $this->assertInstanceOf('Doctrine\DBAL\Statement', $stmt);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $this->assertSame(50, count($results));
        $this->assertSame([
            'id' => '1',
            'name' => 'Book 1',
            'description' => 'The very first book',
            'authors_id' => '1',
        ], $results[0]);
        $this->assertSame([
            'id' => '50',
            'name' => 'Book 50',
            'description' => 'Book 50 description',
            'authors_id' => '3',
        ], $results[49]);
    }

    public function testSimpleExecute()
    {
        $this->loadData('bookshop');
        $results = $this->getSelector()->execute();

        $this->assertSame(50, count($results));
        $this->assertSame([
            'id' => '1',
            'name' => 'Book 1',
            'description' => 'The very first book',
            'authors_id' => '1',
        ], $results[0]);
        $this->assertSame([
            'id' => '50',
            'name' => 'Book 50',
            'description' => 'Book 50 description',
            'authors_id' => '3',
        ], $results[49]);
    }
}
