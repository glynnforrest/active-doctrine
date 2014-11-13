<?php

namespace ActiveDoctrine\Tests\Selector;

use ActiveDoctrine\Selector\MysqlSelector;

use Doctrine\DBAL\Platforms\MysqlPlatform;
use Doctrine\DBAL\Connection;

/**
 * MysqlSelectorTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MysqlSelectorTest extends SelectorTestCase
{

    protected function getSelector(array $types = [])
    {
        $params = ['platform' => new MysqlPlatform];
        $driver = $this->getMock('Doctrine\DBAL\Driver');

        return new MysqlSelector(new Connection($params, $driver), 'table', $types);
    }

}
