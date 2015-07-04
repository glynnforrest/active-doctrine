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
