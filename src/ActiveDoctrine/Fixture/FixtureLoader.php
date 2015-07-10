<?php

namespace ActiveDoctrine\Fixture;

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
        foreach ($this->getSortedFixtures() as $fixture) {
            $fixture->load($connection);
        }
    }

    protected function getSortedFixtures()
    {
        $fixtures = $this->fixtures;
        usort($fixtures, function ($a, $b) {
            if ($a instanceof OrderedFixtureInterface && $b instanceof OrderedFixtureInterface) {
                if ($a->getOrder() === $b->getOrder()) {
                    return 0;
                }

                return $a->getOrder() < $b->getOrder() ? -1 : 1;
            }
            if ($a instanceof OrderedFixtureInterface) {
                return $a->getOrder() === 0 ? 0 : 1;
            }
            if ($b instanceof OrderedFixtureInterface) {
                return $b->getOrder() === 0 ? 0 : -1;
            }

            return 1;
        });

        return $fixtures;
    }
}
