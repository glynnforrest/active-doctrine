# Traits

Traits are available to give reusable behaviours to your entities.

## Timestamps

`ActiveDoctrine\Entity\Traits\TimestampTrait` updates columns when an
entity is inserted and updated. These columns are `created_at` and
`updated_at` by default, though they can be configured. It's even
possible to have more than one column for inserts and updates (for
example, when working with other software using the same database that
expects certain columns).

The columns must be defined in your entity definition and have the
`datetime` type.

### Basic usage

```php
use ActiveDoctrine\Entity\Entity;
use ActiveDoctrine\Entity\Traits\TimestampTrait;

class Article extends Entity
{
    use TimestampTrait;

    protected static $table = `articles';
    protected static $fields = [
        'id',
        'title',
        'slug',
        'created_at',
        'updated_at',
    ];
    protected static $types = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

### Custom column names

```php
use ActiveDoctrine\Entity\Entity;
use ActiveDoctrine\Entity\Traits\TimestampTrait;

class Writer extends Entity
{
    use TimestampTrait;

    protected static $table = 'writers';
    protected static $fields = [
        'id',
        'forename',
        'surname',
        'createdAt',
        'updatedAt',
        'anotherUpdate',
    ];
    protected static $types = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'anotherUpdate' => 'datetime',
    ];
    protected static $insert_timestamps = 'createdAt';
    //use an array for more than one column
    protected static $update_timestamps = [
        'updatedAt',
        'anotherUpdate',
    ];
}
```

## Slugs

`ActiveDoctrine\Entity\Traits\SlugTrait` sets the slugs of one or more
fields to an entity. A good example is storing the slug of an article
title so it can be used in a url.

Set the static `$slugs` property in the entity definition as an array,
with source columns as the keys and slug columns as the values. All
source and slug columns must be defined fields.

When the entity is saved, the value of each source column will be
slugified and set to the corresponding slug column. A slug column will
be skipped if it has been modified, making it easy to override the
slug name.

### Usage

```php

use ActiveDoctrine\Entity\Entity;
use ActiveDoctrine\Entity\Traits\SlugTrait;

class Article extends Entity
{
    use SlugTrait;

    protected static $table = 'articles';
    protected static $fields = [
        'id',
        'title',
        'slug',
        'subtitle',
        'subtitle_slug',
    ];
    protected static $slugs = [
        'title' => 'slug',
        'subtitle' => 'subtitle_slug',
    ];
}
```
