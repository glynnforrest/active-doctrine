<?php

namespace ActiveDoctrine\Tests\Fixture;

use ActiveDoctrine\Fixture\OrderedFixtureInterface;
use Doctrine\DBAL\Connection;

/**
 * OrderedFixtureTwo
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class OrderedFixtureTwo implements OrderedFixtureInterface
{
    public function load(Connection $connection)
    {
        $connection->insert('table', [
            'two' => 2
        ]);
    }

    public function getOrder()
    {
        return 2;
    }
}
