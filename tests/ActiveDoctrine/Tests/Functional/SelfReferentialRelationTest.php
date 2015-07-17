<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Nodes\Node;

/**
 * SelfReferentialRelationTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelfReferentialRelationTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('nodes');
    }

    public function testSelectChildren()
    {
        $this->loadData('nodes');
        $parent = Node::selectOne($this->getConn())
                ->where('id', 1)
                ->with('children')
                ->execute();

        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Nodes\Node', $parent);
        $this->assertSame(5, count($parent->children));

        for ($i = 1; $i < 6; $i++) {
            $child = $parent->children[$i - 1];
            $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Nodes\Node', $child);
            $this->assertSame("Child $i", $child->value);
        }
    }

    public function testSelectParent()
    {
        $this->loadData('nodes');
        $child = Node::selectOne($this->getConn())
                ->where('id', 3)
                ->execute();

        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Nodes\Node', $child);
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Nodes\Node', $child->parent);
        $this->assertSame(1, $child->parent->id);
        $this->assertSame(5, count($child->parent->children));
    }
}
