<?php

namespace ActiveDoctrine\Tests\Fixture;

use ActiveDoctrine\Fixture\FixtureLoader;

/**
 * FixtureLoaderTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FixtureLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->loader = new FixtureLoader();
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    public function testRunNoFixtures()
    {
        $this->conn->expects($this->never())->method('insert');
        $this->loader->run($this->conn);
    }

    public function testRunSingleFixture()
    {
        $this->loader->addFixture(new FixtureOne());

        $this->conn->expects($this->once())
            ->method('insert')
            ->with('table', ['one' => 1]);

        $this->loader->run($this->conn);
    }

    public function testRunTwoFixtures()
    {
        $this->loader->addFixture(new FixtureOne());
        $this->loader->addFixture(new FixtureTwo());

        $this->conn->expects($this->exactly(2))
            ->method('insert')
            ->withConsecutive(
                [$this->equalTo('table'), $this->equalTo(['one' => 1])],
                [$this->equalTo('table'), $this->equalTo(['two' => 2])]
            );

        $this->loader->run($this->conn);
    }

    public function testRunOrderedFixtures()
    {
        //loaded out of order
        $this->loader->addFixture(new OrderedFixtureTwo());
        $this->loader->addFixture(new OrderedFixtureOne());

        $this->conn->expects($this->exactly(2))
            ->method('insert')
            ->withConsecutive(
                [$this->equalTo('table'), $this->equalTo(['one' => 1])],
                [$this->equalTo('table'), $this->equalTo(['two' => 2])]
            );

        $this->loader->run($this->conn);
    }

    public function testTablesAreEmptied()
    {
        $this->loader->addFixture(new FixtureOne());
        $this->loader->addFixture(new OrderedFixtureOne());

        $this->conn->expects($this->once())
            ->method('delete')
            ->with('table', [1 => 1], [\PDO::PARAM_INT]);

        $this->loader->run($this->conn);
    }

    public function testTablesAreNotEmptied()
    {
        $this->loader->addFixture(new FixtureOne());
        $this->loader->addFixture(new OrderedFixtureOne());

        $this->conn->expects($this->never())
            ->method('delete');

        $this->loader->run($this->conn, true);
    }
}
