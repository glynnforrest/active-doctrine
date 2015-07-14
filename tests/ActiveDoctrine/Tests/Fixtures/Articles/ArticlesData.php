<?php

namespace ActiveDoctrine\Tests\Fixtures\Articles;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Fixture\FixtureInterface;

/**
 * ArticlesData
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class ArticlesData implements FixtureInterface
{
    public function load(Connection $connection)
    {
        $records = [];

        for ($i = 1; $i < 21; ++$i) {
            $records[] = ["Article $i"];
        }

        foreach ($records as $r) {
            $connection->insert('articles', [
                'title' => $r[0],
                'slug' => $r[0],
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
            ],
            [
                'created_at' => 'datetime',
                'updated_at' => 'datetime',
            ]);
        }
    }

    public function getTables()
    {
        return ['articles'];
    }
}
