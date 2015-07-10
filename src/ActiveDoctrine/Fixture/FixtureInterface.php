<?php

namespace ActiveDoctrine\Fixture;

use Doctrine\DBAL\Connection;

/**
 * FixtureInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface FixtureInterface
{
    /**
     * Load the data for this fixture into the database.
     *
     * @param Connection $connection
     */
    function load(Connection $connection);
}
