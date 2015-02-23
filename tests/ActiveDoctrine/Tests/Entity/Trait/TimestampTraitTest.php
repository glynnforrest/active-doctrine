<?php

namespace ActiveDoctrine\Tests\Entity\Foo;

use ActiveDoctrine\Tests\Fixtures\Articles\Article;

/**
 * TimestampTraitTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class TimestampTraitTest extends \PHPUnit_Framework_TestCase
{
    protected $conn;

    public function setUp()
    {
        $this->conn = $this->getMockBuilder('Doctrine\DBAL\Connection')
                           ->disableOriginalConstructor()
                           ->getMock();
    }

    public function insertMethodProvider()
    {
        return [
            ['insert'],
            ['save']
        ];
    }

    /**
     * @dataProvider insertMethodProvider
     */
    public function testInsertSetsCreatedAtField($insert_method)
    {
        $article = new Article($this->conn);
        $article->$insert_method();
        $this->assertEquals(new \DateTime(), $article->created_at);
    }

    /**
     * @dataProvider insertMethodProvider
     */
    public function testInsertDoesNotOverrideCreatedAt($insert_method)
    {
        $article = new Article($this->conn);
        $datetime = new \DateTime('2000/1/1');
        $article->created_at = $datetime;
        $article->$insert_method();
        $this->assertSame($datetime, $article->created_at);
    }

}
