<?php

namespace ActiveDoctrine\Tests\Fixture;

use ActiveDoctrine\Fixture\FixtureInterface;
use Doctrine\DBAL\Connection;

/**
 * FixtureOne
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FixtureOne implements FixtureInterface
{
    public function load(Connection $connection)
    {
        $connection->insert('table', [
            'one' => 1
        ]);
    }
}
