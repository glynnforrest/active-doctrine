# Active Doctrine
### A minimal active record implementation on top of the Doctrine DBAL.

[![Build Status](https://travis-ci.org/glynnforrest/active-doctrine.svg)](https://travis-ci.org/glynnforrest/active-doctrine)

# Project goals

* An active record implementation focusing on simplicity and speed.
* Support for a large amount of database vendors by leveraging the DBAL.
* An approximate subset of Doctrine's QueryBuilder for selecting
  entities. Unlike the Doctrine builder which is designed to cover a
  large amount of query possibilities, this builder has a small amount
  of methods that are safe from sql injection.

Installation
------------
Add the following to your composer.json file:

	{
		"require": {
			"glynnforrest/active-doctrine": "dev-master"
		}
	}

And run composer to update your dependencies:

	$ curl -s http://getcomposer.org/installer | php
	$ php composer.phar update

License
-------

MIT
