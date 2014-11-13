<?php

namespace ActiveDoctrine\Tests\Selector;

use ActiveDoctrine\Selector\SqliteSelector;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Connection;

/**
 * SqliteSelectorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SqliteSelectorTest extends SelectorTestCase
{

    protected function getSelector(array $types = [])
    {
        $params = ['platform' => new SqlitePlatform];
        $driver = $this->getMock('Doctrine\DBAL\Driver');

        return new SqliteSelector(new Connection($params, $driver), 'table', $types);
    }

}
