<?php

namespace ActiveDoctrine\Tests\Fixtures\Nodes;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Fixture\FixtureInterface;

/**
 * NodesData
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class NodesData implements FixtureInterface
{
    public function load(Connection $connection)
    {
        $records = [
            [null, 'Parent node'],
            [1, 'Child 1'],
            [1, 'Child 2'],
            [1, 'Child 3'],
            [1, 'Child 4'],
            [1, 'Child 5'],
        ];

        foreach ($records as $r) {
            $connection->insert('nodes', [
                'parent_id' => $r[0],
                'value' => $r[1],
            ]);
        }
    }

    public function getTables()
    {
        return [
            'nodes',
        ];
    }
}
