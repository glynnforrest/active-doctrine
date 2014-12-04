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
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
        $this->repo = new BookRepository($this->conn);
    }

    public function testCreate()
    {
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Entities\Bookshop\Book', $this->repo->create());
    }
}
