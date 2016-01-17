<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\ReservedNames\ReservedTable;

/**
 * Test behaviour with reserved SQL names.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ReservedNamesTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('reservedNames');
    }

    public function testInsertWithReservedTableName()
    {
        $r = new ReservedTable($this->getConn(), ['name' => 'foo']);
        $r->insert();
    }
}
