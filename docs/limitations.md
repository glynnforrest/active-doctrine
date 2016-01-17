# Known limitations

Active Doctrine is designed to be simple and lightweight,
the corollary being it doesn't include every possible ORM feature.

Here is a list of known limitations.
If you find any of these a deal-breaker,
the answer is likely to be "Use Doctrine2 ORM instead".

## It's active record

An inherent limitation of the active record pattern is coupling of
business logic and persistence together.
This violates single responsibility principle and can make business logic harder to test,
since the classes are dependent on the database.

A design decision was made early on to always inject the database connection
and not to rely on a magic way to access it from within entities.
This improves the testing situation slightly because you can create a
mock database connection and inject it into the entity to test,
instead of having to set up a stub database.

## Field names need to repeated when defining entities

If you define types and schema definitions, a field can be repeated up to three times:

```php
class Article extends Entity
{
    protected static $table = 'articles';
    protected static $fields = [
        'id',
        'title',
        'slug',
        'created_at',
        'updated_at',
    ];
    protected static $types = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected static $field_settings = [
        'id' => [
            'length' => 5,
        ],
    ];
}
```

`id` has been repeated 3 times in the different properties of the
class.

Initial steps were made to try and mitigate this,
but were subsequently scrapped in the name of performance.
If mapping configuration was loaded together all at once,
the parsing of column settings, index definitions, etc would be
required each time an entity is loaded.
This process would need to be repeated for every entity at run-time,
often for no reason, since there is no 'load' step
(unlike Doctrine2 for instance, which loads metadata configuration once only).
The compromise therefore is to define $fields, $types and $field_settings in entities,
the bare minimum of which will be used during normal use.

## There is no global entity tracking

There is no concept of an 'object manager' to keep track of entities.

Entities may be queried for again even if an identical object is loaded.
For example, a related entity with an id of 2 may be fetched from the
database more than once in a loop through a collection if eager
loading isn't used.

In addition to this, relationships are one way
(e.g. `$author->book->author !== $author`).
Assuming good entity management you shouldn't ever need to traverse an
entity relationship back to the original
(e.g. book -> author -> book),
but it is something to be aware of.
