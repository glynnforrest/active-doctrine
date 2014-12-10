<?php

namespace ActiveDoctrine\Tests\Fixtures\Events;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Tests\Fixtures\DataInterface;

/**
 * EventsData
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class EventsData implements DataInterface
{

    public function loadData(Connection $connection)
    {
        $records = [
            ['Today', new \DateTime()],
            ['Millennium', new \DateTime('Jan 1 2000')],
        ];

        //pad to 50
        $count = count($records);
        for ($i = $count + 1; $i < 51; $i++) {
            $records[] = ["Event $i", new \DateTime()];
        }

        foreach ($records as $r) {
            $connection->insert('events', [
                'name' => $r[0],
                'start_time' => $r[1]
            ],
            [
                'start_time' => 'datetime'
            ]);
        }
    }

}
