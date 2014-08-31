# Creating entities

In ActiveDoctrine, all entities should subclass
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
        'authors_id'
    ];

}
```

A valid Entity class contains 3 static properties:

* The name of the database table, $table
* The name of the primary key in the database table, $primary_key
* An array containing the names of the columns in the database table, $fields

$primary_key defaults to 'id' so is optional if 'id' is the primary
key on the table.

## Defining associations between entities

There are currently three types of associations available:

* has_one
* has_many
* belongs_to

The syntax for defining these relationships is straightforward. The
static variable `$relations` should be an array, where the keys are
names given to a relationship, and the values are arrays of the form
`[$type, $foreign_class, $foreign_column, $column]`. As a contrived
example, the Book entity could have the following relations:

```php
protected static $relations = [
    'details' => ['has_one', 'ActiveDoctrine\Tests\Entity\BookDetails', 'books_id', 'id'],
    'author' => ['belongs_to', 'ActiveDoctrine\Tests\Entity\Author', 'id', 'authors_id']
];
```

So a Book has a single BookDetails (whatever one of those are), where
the 'id' column joins to the 'books_id' column on the BookDetails
table. A Book also belongs to an Author, where the 'authors_id' column
joins to the 'id' column on the Author table.

The has_many relationship is defined in the Author entity:

```php
protected static $relations = [
    'books' => ['has_many', 'ActiveDoctrine\Tests\Entity\Book', 'authors_id', 'id']
];
```

An Author has many Book instances, where the 'id' column joins to the
'authors_id' column on the Book table.
