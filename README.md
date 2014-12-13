# Active Doctrine
A minimal active record implementation built on top of the Doctrine DBAL.

[![Build Status](https://travis-ci.org/glynnforrest/active-doctrine.svg)](https://travis-ci.org/glynnforrest/active-doctrine)

## Project goals

* An active record implementation focusing on simplicity and ease of use.
* Support for a large amount of database vendors by leveraging the DBAL.
* A select-only query builder for selecting entities. Unlike the
  Doctrine query builder which is designed to cover a large amount of
  query possibilities, this builder has a small amount of methods that
  are safe from sql injection.

## Installation

Add `glynnforrest/active-doctrine` to your composer.json file:

```json
{
    "require": {
        "glynnforrest/active-doctrine": "0.1.*"
    }
}
```

## Quickstart

```php
//create a Doctrine database connection to use
$config = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'user,
    'password' => 'password',
    'dbname' => 'app',
];

$conn = Doctrine\DBAL\DriverManager::getConnection($config);

// insert and update
$author = new App\Entity\Author($conn);

$author->name = 'Glynn';
$author->age = 102;

// insert
$author->save();

$author->age = 100;

// update
$author->save();

// selecting
// SELECT * FROM authors WHERE age > ?
$old_authors = Author::select($conn)
    ->where('age', '>', 100)
    ->execute();

foreach ($old_authors as $author) {
    echo $author->name;
    // books are loaded lazily
    // SELECT * FROM books WHERE authors_id = ?
    foreach ($author->books as $book) {
        echo $book->name;
        echo $book->page_count;
    }
}

// selecting with eager loading
// SELECT * FROM authors WHERE age > ?
// SELECT * FROM books WHERE id IN (?, ?, ?, ?) AND page_count > ?
$old_authors = Author::select($conn)
    ->where('age', '>', 100)
    ->with('books', function($s) {
        $s->where('page_count', '>', 100);
    })
    ->execute();

// deleting
$old_authors->delete();
```

There are many more features. Documentation and examples are in the
`docs/` folder.

## Tests

As well as unit tests, there are functional tests which run against a
real database connection. By default this uses an in-memory sqlite
database, so will fail if the sqlite extension is not set up.

* `phpunit` runs all tests.
* `phpunit --testsuite unit` runs the unit tests only.
* `phpunit --testsuite functional` runs the functional tests only.

### Changing connection parameters

To change the database used in the functional tests, copy
`phpunit.xml.dist` to a new file and set the `db_*` environment
variables.

```xml
<phpunit>
  <!-- Don't change the rest of the file -->

  <php>
    <env name="db_driver" value="pdo_mysql" />
    <env name="db_user" value="root" />
    <env name="db_password" value="" />
    <env name="db_host" value="localhost" />
    <env name="db_name" value="active_doctrine_tests" />
    <env name="db_port" value="3306" />
  </php>
</phpunit>
```

Make sure the target database exists, then run the tests with the new
configuration.

`phpunit -c mysql.xml`

## License

MIT, see LICENSE for details.

Copyright 2014 Glynn Forrest
