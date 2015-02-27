<?php

namespace ActiveDoctrine\Tests\Functional\Traits;

use ActiveDoctrine\Tests\Fixtures\Articles\Article;

/**
 * SlugTraitTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SlugTraitTest extends \PHPUnit_Framework_TestCase
{
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

    public function updateMethodProvider()
    {
        return [
            ['update'],
            ['save'],
        ];
    }

    /**
     * @dataProvider insertMethodProvider
     */
    public function testInsertSetsSlug($insert_method)
    {
        $article = new Article($this->conn);
        $article->title = 'Something something foo';
        $this->assertNull($article->slug);

        $article->$insert_method();

        $this->assertSame('something-something-foo', $article->slug);
    }

    /**
     * @dataProvider insertMethodProvider
     */
    public function testSlugIsNotOverriddenOnInsert($insert_method)
    {
        $article = new Article($this->conn);
        $article->title = 'Something something foo';
        $article->slug = 'custom-slug';

        $article->$insert_method();

        $this->assertSame('custom-slug', $article->slug);
    }

    /**
     * @dataProvider updateMethodProvider
     */
    public function testSlugIsUpdated($update_method)
    {
        $article = new Article($this->conn);
        $article->id = 3;
        $article->title = 'Something something foo';
        $article->slug = 'something-something-foo';
        $article->setStored();

        $article->title = 'Another title';
        $article->$update_method();

        $this->assertSame('another-title', $article->slug);
    }

    /**
     * @dataProvider updateMethodProvider
     */
    public function testSlugIsNotOverriddenOnUpdate($update_method)
    {
        $article = new Article($this->conn);
        $article->id = 3;
        $article->title = 'Something something foo';
        $article->slug = 'something-something-foo';
        $article->setStored();

        $article->title = 'Another title';
        $article->slug = 'custom-slug';
        $article->$update_method();

        $this->assertSame('custom-slug', $article->slug);
    }
}
