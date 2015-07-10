<?php

namespace ActiveDoctrine\Fixture;

/**
 * OrderedFixtureInterface
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
interface OrderedFixtureInterface extends FixtureInterface
{
    /**
     * Get the order in which this fixture should be loaded. Fixtures
     * are loaded in order from low to high.
     *
     * @return int
     */
    public function getOrder();
}
