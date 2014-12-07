# Creating entities

In Active Doctrine, all entities should subclass
ActiveDoctrine\Entity\Entity. Each instance of an Entity represents a
single row in the database table.

```php
namespace MyApp\Entity;

use ActiveDoctrine\Entity\Entity;

class Book extends Entity
{

    protected static $table = 'books';
    protected static $primary_key = 'id';
    protected static $fields = [
        'id',
        'name',
        'description',
        'date_published',
        'authors_id'
    ];

}
```

A valid Entity class contains 3 static properties:

* The name of the database table, `$table`
* The name of the primary key in the database table, `$primary_key` (defaults to 'id')
* An array containing the names of the columns in the database table, `$fields`

The `$relations` and `$types` properties are available for defining
relationships to other entities and the types of the table columns.

## Defining associations between entities

There are currently three types of associations available:

* has_one
* has_many
* belongs_to

The syntax for defining these relationships is straightforward. The
static property `$relations` should be an array, where the keys are
names given to a relationship, and the values are arrays of the form
`[$type, $foreign_class, $foreign_column, $column]`. As a contrived
example, the Book entity could have the following relations:

```php
protected static $relations = [
    'details' => ['has_one', 'MyApp\Entity\BookDetails', 'books_id', 'id'],
    'author' => ['belongs_to', 'MyApp\Entity\Author', 'id', 'authors_id']
];
```

So a Book has a single BookDetails (whatever one of those are), where
the 'id' column joins to the 'books_id' column on the BookDetails
table. A Book also belongs to an Author, where the 'authors_id' column
joins to the 'id' column on the Author table.

The has_many relationship is defined in the Author entity:

```php
protected static $relations = [
    'books' => ['has_many', 'MyApp\Entity\Book', 'authors_id', 'id']
];
```

An Author has many Book instances, where the 'id' column joins to the
'authors_id' column on the Book table.

## Defining types

By leveraging the type system of the DBAL, values can be converted
between their PHP and database representations. Types are set in the
`$types` property, an array of column names and their corresponding
types. If no type is set on a column, it will be returned as a
string. For large result sets the number of type conversions can make
a noticeable difference to execution time, so only define a type if
necessary.

```php
protected static $types = [
    'date_published' => 'date'
];
```

Any Doctrine type is supported, and custom types can be added too.
