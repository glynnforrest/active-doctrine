<?php

namespace ActiveDoctrine\Tests\Selector;

use ActiveDoctrine\Selector\AbstractSelector;
use Doctrine\DBAL\Connection;

/**
 * AbstractSelectorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class AbstractSelectorTest extends \PHPUnit_Framework_TestCase
{

    public function fromConnectionProvider()
    {
        return [
            ['pdo_mysql', 'MysqlSelector'],
            ['pdo_sqlite', 'SqliteSelector']
        ];
    }

    /**
     * @dataProvider fromConnectionProvider()
     */
    public function testFromConnection($name, $class)
    {
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->once())
               ->method('getName')
               ->will($this->returnValue($name));
        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();
        $conn->expects($this->once())
             ->method('getDriver')
             ->will($this->returnValue($driver));

        $selector = AbstractSelector::fromConnection($conn, 'table');
        $this->assertInstanceOf(sprintf('ActiveDoctrine\Selector\%s', $class), $selector);
    }

    public function testFromConnectionThrowsException()
    {
        $driver = $this->getMock('Doctrine\DBAL\Driver');
        $driver->expects($this->once())
               ->method('getName')
               ->will($this->returnValue('foo'));
        $conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                     ->disableOriginalConstructor()
                     ->getMock();
        $conn->expects($this->once())
             ->method('getDriver')
             ->will($this->returnValue($driver));

        $this->setExpectedException('Doctrine\DBAL\DBALException');
        AbstractSelector::fromConnection($conn, 'table');
    }

}
