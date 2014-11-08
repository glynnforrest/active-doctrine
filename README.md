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

## License

MIT, see LICENSE for details.

Copyright 2014 Glynn Forrest
