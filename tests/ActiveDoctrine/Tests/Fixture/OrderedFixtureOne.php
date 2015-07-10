<?php

namespace ActiveDoctrine\Tests\Fixture;

use ActiveDoctrine\Fixture\OrderedFixtureInterface;
use Doctrine\DBAL\Connection;

/**
 * OrderedFixtureOne
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class OrderedFixtureOne implements OrderedFixtureInterface
{
    public function load(Connection $connection)
    {
        $connection->insert('table', [
            'one' => 1
        ]);
    }

    public function getOrder()
    {
        return 1;
    }
}
