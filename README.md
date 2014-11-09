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

Add the following to your composer.json file:

```json
{
    "require": {
        "glynnforrest/active-doctrine": "0.1.*"
    }
}
```

And run composer to update your dependencies:

```bash
curl -s http://getcomposer.org/installer | php
php composer.phar update
```

## Usage

Documentation and usage examples are in the `docs/` folder.

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
