<?php

namespace ActiveDoctrine\Tests\Entity;

use ActiveDoctrine\Entity\EntityCollection;
use ActiveDoctrine\Tests\Fixtures\SetterGetter\UpperCase;

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
        $entities = [new UpperCase($this->conn), new UpperCase($this->conn)];
        $this->assertSame($collection, $collection->setEntities($entities));
        $this->assertSame($entities, $collection->getEntities());
    }

    public function testAddEntitiesOnConstruct()
    {
        $entities = [new UpperCase($this->conn), new UpperCase($this->conn)];
        $collection = new EntityCollection($entities);
        $this->assertSame($entities, $collection->getEntities());
    }

    public function testImplementsIterator()
    {
        $this->assertInstanceOf('\IteratorAggregate', new EntityCollection());
    }

    public function testIterator()
    {
        $item1 = new UpperCase($this->conn);
        $item2 = new UpperCase($this->conn);
        $item3 = new UpperCase($this->conn);
        $collection = new EntityCollection([$item1, $item2, $item3]);

        $this->assertInstanceOf('\ArrayIterator', $iterator = $collection->getIterator());
        $this->assertSame([$item1, $item2, $item3], $iterator->getArrayCopy());
    }

    public function testForeach()
    {
        $item1 = new UpperCase($this->conn);
        $item2 = new UpperCase($this->conn);
        $item3 = new UpperCase($this->conn);
        $expected = [$item1, $item2, $item3];
        $collection = new EntityCollection($expected);

        $results = [];
        foreach ($collection as $item) {
            $results[] = $item;
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
        $collection->setEntities([new UpperCase($this->conn)]);
        $this->assertSame(1, count($collection));
        $collection->setEntities([new UpperCase($this->conn), new UpperCase($this->conn)]);
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

        $item1 = new UpperCase($this->conn);
        $item2 = new UpperCase($this->conn);

        $collection[] = $item1;
        $this->assertSame($item1, $collection[0]);

        $collection[] = $item2;
        $this->assertSame($item2, $collection[1]);

        $collection[0] = $item2;
        $collection[1] = $item1;

        $this->assertSame($item1, $collection[1]);
        $this->assertSame($item2, $collection[0]);
        $this->assertFalse(isset($collection[3]));

        $this->assertSame([$item2, $item1], $collection->getEntities());

        unset($collection[1]);
        $this->assertFalse(isset($collection[1]));
        $this->assertNull($collection[1]);
        $this->assertSame([$item2], $collection->getEntities());

        unset($collection[0]);
        $this->assertFalse(isset($collection[0]));
        $this->assertNull($collection[0]);
        $this->assertSame([], $collection->getEntities());
    }

    public function testOffsetSetFailsForNonNumericKeys()
    {
        $collection = new EntityCollection();
        $this->setExpectedException('\InvalidArgumentException');
        $collection['foo'] = new UpperCase($this->conn);
    }

    public function testGetColumn()
    {
        $collection = new EntityCollection();
        $collection[] = new UpperCase($this->conn, ['description' => 'foo']);
        $collection[] = new UpperCase($this->conn);
        $collection[] = new UpperCase($this->conn, ['description' => 'bar']);
        $this->assertSame(['FOO', '', 'BAR'], $collection->getColumn('description'));
    }

    public function testGetColumnRaw()
    {
        $collection = new EntityCollection();
        $collection[] = new UpperCase($this->conn, ['description' => 'foo']);
        $collection[] = new UpperCase($this->conn);
        $collection[] = new UpperCase($this->conn, ['description' => 'bar']);
        $this->assertSame(['foo', null, 'bar'], $collection->getColumnRaw('description'));
    }

    public function testGetEntitiesChunked()
    {
        $collection = new EntityCollection();
        for ($i = 1; $i < 9; $i++) {
            ${'item' . $i} = new UpperCase($this->conn);
            $collection[] = ${'item' . $i};
        }
        $expected = [
            [$item1, $item2, $item3],
            [$item4, $item5, $item6],
            [$item7, $item8]
        ];
        $this->assertSame($expected, $collection->getEntitiesChunked(3));
    }

    public function testSetColumn()
    {
        $collection = new EntityCollection();
        $collection[] = new UpperCase($this->conn, ['name' => 'foo']);
        $collection[] = new UpperCase($this->conn);
        $collection[] = new UpperCase($this->conn, ['name' => 'bar']);
        $collection->setColumn('name', 'changed');
        $this->assertSame(['CHANGED', 'CHANGED', 'CHANGED'], $collection->getColumn('name'));
    }

    public function testSetColumnRaw()
    {
        $collection = new EntityCollection();
        $collection[] = new UpperCase($this->conn, ['name' => 'foo']);
        $collection[] = new UpperCase($this->conn);
        $collection[] = new UpperCase($this->conn, ['name' => 'bar']);
        $collection->setColumnRaw('name', 'changed');
        $this->assertSame(['changed', 'changed', 'changed'], $collection->getColumn('name'));
    }

    public function testSave()
    {
        for ($i = 1; $i < 4; $i++) {
            ${'item' . $i} = $this->getMockBuilder('ActiveDoctrine\Tests\Fixtures\SetterGetter\UpperCase')
                                  ->disableOriginalConstructor()
                                  ->getMock();
            ${'item' . $i}->expects($this->once())
                                ->method('save');
        }
        $collection = new EntityCollection();
        $collection->setEntities([$item1, $item2, $item3]);

        $this->assertSame($collection, $collection->save());
    }

    public function testGetOne()
    {
        for ($i = 1; $i < 4; $i++) {
            ${'item' . $i} = new UpperCase($this->conn, ['name' => 'item' . $i]);
        }
        $collection = new EntityCollection;
        $collection->setEntities([$item1, $item2, $item3]);

        $this->assertSame($item3, $collection->getOne('name', 'item3'));

        $this->assertNull($collection->getOne('name', 'foo'));
    }

    public function testRemove()
    {
        for ($i = 1; $i < 4; $i++) {
            ${'item' . $i} = new UpperCase($this->conn, ['name' => 'item' . $i]);
        }
        $collection = new EntityCollection;
        $collection->setEntities([$item1, $item2, $item3]);

        $this->assertSame($item2, $collection->remove('name', 'item2'));
        $this->assertSame([$item1, $item3], $collection->getEntities());
        $this->assertNull($collection->remove('name', 'item2'));

        $this->assertSame($item3, $collection->remove('name', 'item3'));
        $this->assertSame([$item1], $collection->getEntities());
        $this->assertNull($collection->remove('name', 'item3'));

        $this->assertSame($item1, $collection->remove('name', 'item1'));
        $this->assertSame([], $collection->getEntities());
        $this->assertNull($collection->remove('name', 'item1'));
    }

    public function testGetOneAndRemoveUseGet()
    {
        $collection = new EntityCollection;
        for ($i = 1; $i < 4; $i++) {
            ${'item' . $i} = new UpperCase($this->conn, ['description' => 'item' . $i]);
        }
        $collection->setEntities([$item1, $item2, $item3]);

        //item has a getterDescription() method that returns the upper case description
        $this->assertSame($item2, $collection->getOne('description', 'ITEM2'));
        $this->assertSame($item2, $collection->remove('description', 'ITEM2'));
    }

    public function testFilter()
    {
        $collection = new EntityCollection;
        for ($i = 1; $i < 9; $i++) {
            ${'item' . $i} = $item = new UpperCase($this->conn, ['name' => 'item' . $i, 'description' => 'item' . $i]);
            $collection[] = $item;
        }

        $callback = function ($entity) {
            return $entity->description === 'ITEM3' || $entity->name === 'item1';
        };

        $filtered = $collection->filter($callback);
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $filtered);
        $this->assertSame([$item1, $item3], $filtered->getEntities());
    }

}
