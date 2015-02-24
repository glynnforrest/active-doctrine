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

    public function updateMethodProvider()
    {
        return [
            ['update'],
            ['save']
        ];
    }

    /**
     * @dataProvider updateMethodProvider
     */
    public function testUpdateSetsUpdatedAtField($update_method)
    {
        $article = new Article($this->conn, ['id' => 1]);
        $article->setStored();
        $article->$update_method();
        $this->assertEquals(new \DateTime(), $article->updated_at);
    }

    /**
     * @dataProvider updateMethodProvider
     */
    public function testUpdateDoesNotOverrideUpdatedAt($update_method)
    {
        $article = new Article($this->conn, ['id' => 1]);
        $article->setStored();
        $datetime = new \DateTime('2000/1/1');
        $article->updated_at = $datetime;
        $article->$update_method();
        $this->assertSame($datetime, $article->updated_at);
    }
}
