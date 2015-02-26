<?php

namespace ActiveDoctrine\Tests\Entity\Foo;

use ActiveDoctrine\Tests\Fixtures\Articles\Article;
use ActiveDoctrine\Tests\Fixtures\Articles\Writer;

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
            ['save'],
        ];
    }

    /**
     * @dataProvider insertMethodProvider
     */
    public function testInsertSetsCreatedAtAndUpdatedAt($insert_method)
    {
        $article = new Article($this->conn);
        $article->$insert_method();

        $this->assertEquals(new \DateTime(), $article->created_at);
        $this->assertEquals(new \DateTime(), $article->updated_at);
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

    /**
     * @dataProvider insertMethodProvider
     */
    public function testInsertDoesNotOverrideUpdatedAt($insert_method)
    {
        $article = new Article($this->conn);
        $datetime = new \DateTime('2000/1/1');
        $article->updated_at = $datetime;
        $article->$insert_method();
        $this->assertSame($datetime, $article->updated_at);
    }

    /**
     * @dataProvider insertMethodProvider
     */
    public function testInsertFieldsCanBeConfigured($insert_method)
    {
        $writer = new Writer($this->conn);
        $writer->$insert_method();

        $datetime = new \DateTime();
        $this->assertEquals($datetime, $writer->createdAt);
        $this->assertEquals($datetime, $writer->anotherCreate);
        $this->assertEquals($datetime, $writer->updatedAt);
        $this->assertEquals($datetime, $writer->anotherUpdate);
    }

    public function updateMethodProvider()
    {
        return [
            ['update'],
            ['save'],
        ];
    }

    /**
     * @dataProvider updateMethodProvider
     */
    public function testUpdateSetsUpdatedAtField($update_method)
    {
        $article = new Article($this->conn, ['id' => 1]);
        $article->setStored();
        $article->title = 'foo';
        $article->$update_method();
        $this->assertEquals(new \DateTime(), $article->updated_at);
    }

    /**
     * @dataProvider updateMethodProvider
     */
    public function testUpdateFieldsCanBeConfigured($update_method)
    {
        $writer = new Writer($this->conn, ['id' => 1]);
        $writer->setStored();
        $writer->forename = 'Glynn';
        $writer->$update_method();

        $datetime = new \DateTime();
        $this->assertEquals($datetime, $writer->updatedAt);
        $this->assertEquals($datetime, $writer->anotherUpdate);
        //check that other fields haven't been set
        $this->assertNull($writer->createdAt);
        $this->assertNull($writer->anotherCreate);
    }
}
