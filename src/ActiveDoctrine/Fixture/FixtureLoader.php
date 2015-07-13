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
    protected $dropTables = [];
    protected $fixtures = [];

    /**
     * Add a fixture to be loaded into the database.
     *
     * @param FixtureInterface $fixture
     */
    public function addFixture(FixtureInterface $fixture)
    {
        $this->dropTables = array_merge($this->dropTables, (array) $fixture->getTables());
        $this->fixtures[] = $fixture;
    }

    /**
     * Run all fixtures.
     *
     * @param Connection $connection
     * @param bool       $append     Don't empty tables before loading
     */
    public function run(Connection $connection, $append = false)
    {
        if (!$append) {
            //in the future, resolve key constraints here
            foreach (array_unique($this->dropTables) as $table) {
                $connection->delete($table, [1 => 1], [\PDO::PARAM_INT]);
            }
        }

        foreach ($this->getSortedFixtures() as $fixture) {
            $this->runFixture($connection, $fixture);
        }
    }

    /**
     * Run a fixture. Override this method to add logging, etc.
     *
     * @param Connection       $connection
     * @param FixtureInterface $fixture
     */
    protected function runFixture(Connection $connection, FixtureInterface $fixture)
    {
        $fixture->load($connection);
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
