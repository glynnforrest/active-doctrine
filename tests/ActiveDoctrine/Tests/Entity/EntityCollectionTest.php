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

    public function testSetAndGetEntities()
    {
        $collection = new EntityCollection();
        $this->assertSame([], $collection->getEntities());
        $entities = [new Book($this->conn), new Book($this->conn)];
        $this->assertSame($collection, $collection->setEntities($entities));
        $this->assertSame($entities, $collection->getEntities());
    }

    public function testAddEntitiesOnConstruct()
    {
        $entities = [new Book($this->conn), new Book($this->conn)];
        $collection = new EntityCollection($entities);
        $this->assertSame($entities, $collection->getEntities());
    }

    public function testImplementsIterator()
    {
        $this->assertInstanceOf('\IteratorAggregate', new EntityCollection());
    }

    public function testIterator()
    {
        $book1 = new Book($this->conn);
        $book2 = new Book($this->conn);
        $book3 = new Book($this->conn);
        $collection = new EntityCollection([$book1, $book2, $book3]);

        $this->assertInstanceOf('\ArrayIterator', $iterator = $collection->getIterator());
        $this->assertSame([$book1, $book2, $book3], $iterator->getArrayCopy());
    }

    public function testForeach()
    {
        $book1 = new Book($this->conn);
        $book2 = new Book($this->conn);
        $book3 = new Book($this->conn);
        $expected = [$book1, $book2, $book3];
        $collection = new EntityCollection($expected);

        $results = [];
        foreach ($collection as $book) {
            $results[] = $book;
        }

        $this->assertSame($expected, $results);
    }

    public function testImplementsCountable()
    {
        $this->assertInstanceOf('\Countable', new EntityCollection());
    }

    public function testCount()
    {
        $collection = new EntityCollection();
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
        $this->assertInstanceOf('\ArrayAccess', new EntityCollection());
    }

    public function testArrayAccess()
    {
        $collection = new EntityCollection();

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
        $collection = new EntityCollection();
        $this->setExpectedException('\InvalidArgumentException');
        $collection['foo'] = new Book($this->conn);
    }

    public function testGetColumn()
    {
        $collection = new EntityCollection();
        $collection[] = new Book($this->conn, ['name' => 'foo']);
        $collection[] = new Book($this->conn);
        $collection[] = new Book($this->conn, ['name' => 'bar']);
        $this->assertSame(['foo', null, 'bar'], $collection->getColumn('name'));
    }

    public function testGetEntitiesChunked()
    {
        $collection = new EntityCollection();
        for ($i = 1; $i < 9; $i++) {
            ${'book' . $i} = new Book($this->conn);
            $collection[] = ${'book' . $i};
        }
        $expected = [
            [$book1, $book2, $book3],
            [$book4, $book5, $book6],
            [$book7, $book8]
        ];
        $this->assertSame($expected, $collection->getEntitiesChunked(3));
    }

    public function testSetColumn()
    {
        $collection = new EntityCollection();
        $collection[] = new Book($this->conn, ['name' => 'foo']);
        $collection[] = new Book($this->conn);
        $collection[] = new Book($this->conn, ['name' => 'bar']);
        $collection->setColumn('name', 'changed');
        $this->assertSame(['CHANGED', 'CHANGED', 'CHANGED'], $collection->getColumn('name'));
    }

    public function testSave()
    {
        for ($i = 1; $i < 4; $i++) {
            ${'book' . $i} = $this->getMockBuilder('ActiveDoctrine\Tests\Entity\Book')
                                  ->disableOriginalConstructor()
                                  ->getMock();
            ${'book' . $i}->expects($this->once())
                                ->method('save');
        }
        $collection = new EntityCollection();
        $collection->setEntities([$book1, $book2, $book3]);

        $this->assertSame($collection, $collection->save());
    }

    public function testGetOne()
    {
        for ($i = 1; $i < 4; $i++) {
            ${'book' . $i} = new Book($this->conn, ['name' => 'book' . $i]);
        }
        $collection = new EntityCollection;
        $collection->setEntities([$book1, $book2, $book3]);

        $this->assertSame($book3, $collection->getOne('name', 'book3'));

        $this->assertNull($collection->getOne('name', 'foo'));
    }

    public function testRemove()
    {
        for ($i = 1; $i < 4; $i++) {
            ${'book' . $i} = new Book($this->conn, ['name' => 'book' . $i]);
        }
        $collection = new EntityCollection;
        $collection->setEntities([$book1, $book2, $book3]);

        $this->assertSame($book2, $collection->remove('name', 'book2'));
        $this->assertSame([$book1, $book3], $collection->getEntities());
        $this->assertNull($collection->remove('name', 'book2'));

        $this->assertSame($book3, $collection->remove('name', 'book3'));
        $this->assertSame([$book1], $collection->getEntities());
        $this->assertNull($collection->remove('name', 'book3'));

        $this->assertSame($book1, $collection->remove('name', 'book1'));
        $this->assertSame([], $collection->getEntities());
        $this->assertNull($collection->remove('name', 'book1'));
    }

    public function testGetOneAndRemoveUseGet()
    {
        $collection = new EntityCollection;
        for ($i = 1; $i < 4; $i++) {
            ${'book' . $i} = new Book($this->conn, ['description' => 'book' . $i]);
        }
        $collection->setEntities([$book1, $book2, $book3]);

        //book has a getterDescription() method that returns the upper case description
        $this->assertSame($book2, $collection->getOne('description', 'BOOK2'));
        $this->assertSame($book2, $collection->remove('description', 'BOOK2'));
    }

    public function testFilter()
    {
        $collection = new EntityCollection;
        for ($i = 1; $i < 9; $i++) {
            ${'book' . $i} = $book = new Book($this->conn, ['name' => 'book' . $i, 'description' => 'book' . $i]);
            $collection[] = $book;
        }

        $callback = function($entity) {
            return $entity->description === 'BOOK3' || $entity->name === 'book1';
        };

        $filtered = $collection->filter($callback);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $filtered);
        $this->assertSame([$book1, $book3], $filtered->getEntities());
    }

}
