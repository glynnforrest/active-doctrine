<?php

namespace ActiveDoctrine\Tests\Selector;

use ActiveDoctrine\Selector\MysqlSelector;

/**
 * MysqlSelectorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MysqlSelectorTest extends SelectorTestCase
{

    protected function getSelector()
    {
        return new MysqlSelector('table');
    }

}
