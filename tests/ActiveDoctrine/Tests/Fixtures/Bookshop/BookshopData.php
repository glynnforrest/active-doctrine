<?php

namespace ActiveDoctrine\Tests\Fixtures\Bookshop;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Tests\Fixtures\DataInterface;

/**
 * BookshopData
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class BookshopData implements DataInterface
{

    public function loadData(Connection $connection)
    {
        $records = [
            ['Book 1', 'The very first book', 1],
            ['Book 2', 'The second book', 2],
        ];

        //pad to 50
        $count = count($records);
        for ($i = $count + 1; $i < 51; $i++) {
            $records[] = ["Book $i", "Book $i description", 3];
        }

        foreach ($records as $r) {
            $connection->insert('books', [
                'name' => $r[0],
                'description' => $r[1],
                'authors_id' => $r[2]
            ]);
        }
        $this->loadDetails($connection);
        $this->loadAuthors($connection);
    }

    protected function loadDetails(Connection $connection)
    {
        $records = [
            [2, 'Something something something', 100, 10],
            [3, 'Something something something', 100, 10],
            [4, 'Something something something', 100, 10],
            [5, 'Something something something', 100, 10],
            [6, 'Something something something', 100, 10],
            [7, 'Something something something', 100, 10],
            [8, 'Something something something', 100, 10],
            [9, 'Something something something', 100, 10],
            [10, 'Something something something', 100, 10],
            [11, 'Something something something', 100, 10]
        ];

        foreach ($records as $r) {
            $connection->insert('book_details', [
                'books_id' => $r[0],
                'synopsis' => $r[1],
                'pages' => $r[2],
                'chapters' => $r[3]
            ]);
        }
    }

    protected function loadAuthors(Connection $connection)
    {
        $records = [
            ['Thomas Hardy'],
            ['Terry Pratchett'],
            ['Malcolm Gladwell'],
            ['Charles Dickens'],
        ];

        foreach ($records as $r) {
            $connection->insert('authors', [
                'name' => $r[0]
            ]);
        }
    }
}
