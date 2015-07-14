<?php

namespace ActiveDoctrine\Tests\Fixtures\MusicFestival;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Fixture\FixtureInterface;

/**
 * MusicFestivalData
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class MusicFestivalData implements FixtureInterface
{
    public function load(Connection $connection)
    {
        $records = [
            ['Today', new \DateTime()],
            ['Millennium', new \DateTime('Jan 1 2000')],
        ];

        //pad to 50
        $count = count($records);
        for ($i = $count + 1; $i < 51; $i++) {
            $records[] = ["Performance $i", new \DateTime()];
        }

        foreach ($records as $r) {
            $connection->insert('performances', [
                'name' => $r[0],
                'start_time' => $r[1]
            ],
            [
                'start_time' => 'datetime'
            ]);
        }
    }

    public function getTables()
    {
        return ['performances'];
    }

}
