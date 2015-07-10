<?php

namespace ActiveDoctrine\Fixture;

use ActiveDoctrine\Fixture\FixtureInterface;
use Doctrine\DBAL\Connection;

/**
 * FixtureLoader
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class FixtureLoader
{
    protected $fixtures = [];

    /**
     * Add a fixture to be loaded into the database.
     *
     * @param FixtureInterface $fixture
     */
    public function addFixture(FixtureInterface $fixture)
    {
        $this->fixtures[] = $fixture;
    }

    /**
     * Run all fixtures.
     *
     * @param Connection $connection
     */
    public function run(Connection $connection)
    {
        foreach ($this->fixtures as $fixture) {
            $fixture->load($connection);
        }
    }
}
