<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\MusicFestival\Performance;

/**
 * SerializeTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SerializeTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->loadSchema('music_festival');
    }

    public function testSerializeKeepsProperties()
    {
        $now = new \DateTime();
        $performance = new Performance($this->getConn(), [
            'name' => 'foo',
            'start_time' => $now
        ]);
        $performance->insert();

        //simulate caching the entity
        $cache = serialize($performance);
        $cached_performance = unserialize($cache);

        $this->assertEquals($performance->getValues(), $cached_performance->getValues());
        $this->assertTrue($cached_performance->isStored());
    }
}
