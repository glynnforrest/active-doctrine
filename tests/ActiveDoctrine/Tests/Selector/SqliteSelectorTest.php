<?php

namespace ActiveDoctrine\Tests\Selector;

use ActiveDoctrine\Selector\SqliteSelector;

/**
 * SqliteSelectorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SqliteSelectorTest extends SelectorTestCase
{

    protected function getSelector()
    {
        return new SqliteSelector('table');
    }

}
