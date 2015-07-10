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
    function load(Connection $connection);
}
