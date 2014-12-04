<?php

namespace ActiveDoctrine\Tests\Repository;

use ActiveDoctrine\Tests\Fixtures\Repository\BookRepository;

/**
 * AbstractRepositoryTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AbstractRepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected $repo;
    protected $conn;

    public function setUp()
    {
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->any())
               ->method('getName')
               ->will($this->returnValue('pdo_mysql'));
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->conn->expects($this->any())
                   ->method('getDriver')
                   ->will($this->returnValue($driver));
        $this->repo = new BookRepository($this->conn);
    }

    public function testCreate()
    {
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book', $this->repo->create());
    }

    public function testFindAll()
    {
        $stmt = $this->getMockBuilder('Doctrine\DBAL\Statement')
                     ->disableOriginalConstructor()
                     ->getMock();
        $stmt->expects($this->once())
             ->method('execute')
             ->with([]);
        $stmt->expects($this->exactly(3))
             ->method('fetch')
             ->will($this->onConsecutiveCalls(
                 ['name' => 'foo', 'id' => 4],
                 ['name' => 'bar', 'id' => 5],
                 false
             ));
        $this->conn->expects($this->once())
                   ->method('prepare')
                   ->with('SELECT * FROM `books`')
                   ->will($this->returnValue($stmt));

        $books = $this->repo->findAll();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $books);
        $this->assertSame(2, count($books));
        $this->assertSame(['foo', 'bar'], $books->getColumn('name'));
    }
}
