<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\EntityCollection;

/**
 * EntityCollectionTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EntityCollectionTest extends \PHPUnit_Framework_TestCase
{

    protected $conn;

    public function setUp()
    {
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
    }

    public function testSetAndGetTable()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertNull($collection->getTable());
        $this->assertSame($collection, $collection->setTable('foo'));
        $this->assertSame('foo', $collection->getTable());
    }

    public function testSetAndGetFields()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertSame([], $collection->getFields());
        $this->assertSame($collection, $collection->setFields(['id', 'foo', 'bar']));
        $this->assertSame(['id', 'foo', 'bar'], $collection->getFields());
    }

    public function testSetAndGetPrimaryKey()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertNull($collection->getPrimaryKey());
        $this->assertSame($collection, $collection->setPrimaryKey('id'));
        $this->assertSame('id', $collection->getPrimaryKey());
    }

    public function testSetAndGetEntityClass()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertNull($collection->getEntityClass());
        $this->assertSame($collection, $collection->setEntityClass('ActiveDoctrine\Tests\Entity\Book'));
        $this->assertSame('ActiveDoctrine\Tests\Entity\Book', $collection->getEntityClass());
    }

    public function testSetAndGetEntities()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertSame([], $collection->getEntities());
        $entities = [new Book($this->conn), new Book($this->conn)];
        $this->assertSame($collection, $collection->setEntities($entities));
        $this->assertSame($entities, $collection->getEntities());
    }

    public function testAddEntitiesOnConstruct()
    {
        $entities = [new Book($this->conn), new Book($this->conn)];
        $collection = new EntityCollection($this->conn, $entities);
        $this->assertSame($entities, $collection->getEntities());
    }

    public function testImplementsIterator()
    {
        $this->assertInstanceOf('\Iterator', new EntityCollection($this->conn));
    }

    public function testIterator()
    {
        $book1 = new Book($this->conn);
        $book2 = new Book($this->conn);
        $book3 = new Book($this->conn);
        $collection = new EntityCollection($this->conn, [$book1, $book2, $book3]);

        $collection->rewind();
        $this->assertTrue($collection->valid());
        $this->assertSame(0, $collection->key());
        $this->assertSame($book1, $collection->current());
        $collection->next();

        $this->assertTrue($collection->valid());
        $this->assertSame(1, $collection->key());
        $this->assertSame($book2, $collection->current());
        $collection->next();

        $this->assertTrue($collection->valid());
        $this->assertSame(2, $collection->key());
        $this->assertSame($book3, $collection->current());
        $collection->next();

        $this->assertFalse($collection->valid());

        $collection->rewind();
        $this->assertTrue($collection->valid());
        $this->assertSame(0, $collection->key());
        $this->assertSame($book1, $collection->current());
    }

    public function testForeach()
    {
        $book1 = new Book($this->conn);
        $book2 = new Book($this->conn);
        $book3 = new Book($this->conn);
        $expected = [$book1, $book2, $book3];
        $collection = new EntityCollection($this->conn, $expected);

        $results = [];
        foreach ($collection as $book) {
            $results[] = $book;
        }

        $this->assertSame($expected, $results);
    }

    public function testImplementsCountable()
    {
        $this->assertInstanceOf('\Countable', new EntityCollection($this->conn));
    }

    public function testCount()
    {
        $collection = new EntityCollection($this->conn);
        $this->assertSame(0, count($collection));
        $collection->setEntities([new Book($this->conn)]);
        $this->assertSame(1, count($collection));
        $collection->setEntities([new Book($this->conn), new Book($this->conn)]);
        $this->assertSame(2, count($collection));
        $collection->setEntities([]);
        $this->assertSame(0, count($collection));
    }

    public function testImplementsArrayAccess()
    {
        $this->assertInstanceOf('\ArrayAccess', new EntityCollection($this->conn));
    }

    public function testArrayAccess()
    {
        $collection = new EntityCollection($this->conn);

        $this->assertNull($collection[0]);
        $this->assertNull($collection[3]);
        $this->assertFalse(isset($collection[0]));
        $this->assertFalse(isset($collection[3]));

        $book1 = new Book($this->conn);
        $book2 = new Book($this->conn);

        $collection[] = $book1;
        $this->assertSame($book1, $collection[0]);

        $collection[] = $book2;
        $this->assertSame($book2, $collection[1]);

        $collection[0] = $book2;
        $collection[1] = $book1;

        $this->assertSame($book1, $collection[1]);
        $this->assertSame($book2, $collection[0]);
        $this->assertFalse(isset($collection[3]));

        $this->assertSame([$book2, $book1], $collection->getEntities());

        unset($collection[1]);
        $this->assertFalse(isset($collection[1]));
        $this->assertNull($collection[1]);
        $this->assertSame([$book2], $collection->getEntities());

        unset($collection[0]);
        $this->assertFalse(isset($collection[0]));
        $this->assertNull($collection[0]);
        $this->assertSame([], $collection->getEntities());
    }

    public function testOffsetSetFailsForNonNumericKeys()
    {
        $collection = new EntityCollection($this->conn);
        $this->setExpectedException('\InvalidArgumentException');
        $collection['foo'] = new Book($this->conn);
    }

}
