<?php

namespace ActiveDoctrine\Tests\Fixture;

use ActiveDoctrine\Fixture\FixtureInterface;
use Doctrine\DBAL\Connection;

/**
 * FixtureTwo
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FixtureTwo implements FixtureInterface
{
    public function load(Connection $connection)
    {
        $connection->insert('table', [
            'two' => 2
        ]);
    }

    public function getTables()
    {
        return ['table'];
    }
}
