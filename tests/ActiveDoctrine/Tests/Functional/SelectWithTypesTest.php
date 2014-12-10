<?php

namespace ActiveDoctrine\Tests\Functional;

use ActiveDoctrine\Tests\Fixtures\Events\Event;

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
        $this->loadSchema('events');
        $this->loadData('events');
        $events = Event::select($this->getConn())
            ->where('start_time', '=', $date)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Entity\EntityCollection', $events);
        $this->assertSame(1, count($events));
        $event = $events[0];
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Events\Event', $event);
        $this->assertSame('Millennium', $event->name);
        $this->assertEquals($date, $event->start_time);
    }

    public function testSelectOneTypeConversion()
    {
        $date = new \DateTime('2000-01-01');
        $this->loadSchema('events');
        $this->loadData('events');
        $event = Event::selectOne($this->getConn())
            ->where('start_time', '=', $date)
            ->execute();
        $this->assertInstanceOf('ActiveDoctrine\Tests\Fixtures\Events\Event', $event);
        $this->assertSame('Millennium', $event->name);
        $this->assertEquals($date, $event->start_time);
    }

}
