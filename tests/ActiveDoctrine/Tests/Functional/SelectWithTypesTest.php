<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\MusicFestival\Performance;

/**
 * SelectWithTypesTest
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SelectWithTypesTest extends FunctionalTestCase
{
    public function testSelectTypeConversion()
    {
        $date = new \DateTime('2000-01-01');
        $this->loadSchema('music_festival');
        $this->loadData('music_festival');
        $perfs = Performance::select($this->getConn())
            ->where('start_time', '=', $date)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $perfs);
        $this->assertSame(1, count($perfs));
        $perf = $perfs[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\MusicFestival\Performance', $perf);
        $this->assertSame('Millennium', $perf->name);
        $this->assertEquals($date, $perf->start_time);
    }

    public function testSelectOneTypeConversion()
    {
        $date = new \DateTime('2000-01-01');
        $this->loadSchema('music_festival');
        $this->loadData('music_festival');
        $perf = Performance::selectOne($this->getConn())
            ->where('start_time', '=', $date)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\MusicFestival\Performance', $perf);
        $this->assertSame('Millennium', $perf->name);
        $this->assertEquals($date, $perf->start_time);
    }
}
