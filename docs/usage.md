# Using Active Doctrine

## Setup

After creating entity classes, the only requirement to use Active
Doctrine is a Doctrine connection instance.

```php
$config = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'user',
    'password' => 'password',
    'dbname' => 'app',
];

$conn = Doctrine\DBAL\DriverManager::getConnection($config);
```

## Creating entities

Create a new entity by injecting the connection instance.

```php
$author = new App\Entity\Author($conn);
```

Set values on the entities by calling either `set()` or
`setRaw()`. The magic method `__set()` uses `set()` under the hood.

Get these values by using `get()`, `getRaw()` or `__get()`.

```php
$author->set('name', 'Glynn');
echo $author->get('name');
// Glynn

$author->setRaw('age', 100);
echo $author->getRaw('age');
// 100

$author->age = 80;
echo $author->age;
// 80
```

Arbitrary fields not included in the Entity definition can be set too.

```php
$author->foo = 'foo';
echo $author->foo;
// foo
```

Values can also be assigned when creating the entity.

```php
$author = new App\Entity\Author($conn, ['name' => 'Glynn', 'age' => 100]);
```

### Getters and setters

Getter and setter methods can be used to modify field values when they
are set or fetched.

`get()` and `set()` check for getter and setter methods if they exist,
whereas `getRaw()` and `setRaw()` don't. Valid methods begin with
'getter' or 'setter', followed by the name of the field.

Values passed to `__construct()` also won't use setter and getter methods.

```php
class UpperCase extends ActiveDoctrine\Entity\Entity
{
    protected static $fields = [
        'name',
        'description',
    ];

    public function setterName($name)
    {
        return strtoupper($name);
    }

    public function getterDescription()
    {
        return strtoupper($this->getRaw('description'));
    }
}

$item = new UpperCase($conn, ['description' => 'bar']);

$item->setRaw('name', 'foo');
echo $item->get('name');
// foo

$item->name = 'foo';
echo $item->get('name');
// FOO
echo $item->getRaw('name');
// FOO

echo $item->get('description');
// BAR
echo $item->getRaw('description');
// bar
```

### Has

Use `has()` to check if an Entity column or relation has a value.

```php
$book = new Book($conn, ['name' => 'The Art of War']);

$book->has('name');
// true

$book->has('description');
//false
```

Related objects will automatically be queried for if they haven't
been already.

```php
$book->has('author')
//query for author

$book = Book::selectOne($conn)->with('author')->execute();
$book->has('author');
//author already queried for, do not query again
```

A note about `__isset()`:

It may seem intuitive for `__isset()` to behave like `has()`. However,
`__isset()` exists solely as a utility method for libraries that
attempt to access object properties, e.g. Twig. This method always
returns `true`, just like `get()` never throws an error (returning
null for no value). This makes it easy for libraries such as Twig to
access fields using the syntax `entity.field`. Always use `has()` to
check if an entity column or relation has a value.

## Insert, update and delete

Use `save()` to persist an Entity to the database. A decision will be
made to use an insert or update query automatically, though this can
be overridden by using `insert()` and `update()`.

A query will only be executed if the entity is modified.

After persisting, the primary key of an entity is assigned
automatically.

```php

$author = new App\Entity\Author($conn, ['name' => 'Glynn', 'age' => 100]);

// insert
$author->save();
// or use insert()

// nothing has changed, so no query
$author->save();

$author->age = 80;

// update
$author->save();
// or use update()

// nothing has changed, so no query
$author->save();
```

Use `delete()` to remove the entity from the database.

```php
$author->delete();
```

## Select

Use `select()` to select entities from the database. This creates an
`ActiveDoctrine\Entity\EntitySelector` instance to build the
query. After building, call `execute()` to get the results.

```php

// SELECT * FROM authors
$authors = Author::select($conn)->execute();

// SELECT * FROM authors WHERE age > ?
$old_authors = Author::select($conn)
    ->where('age', '>', 100)
    ->execute();

// SELECT
$young_authors = Author::select($conn)
    ->where('age', '<', 25)
    ->orderBy('age', 'ASC')
    ->execute();
```

### Results

Results are instances of `ActiveDoctrine\Entity\EntityCollection`,
which can be iterated over and have methods for manipulating the
entities. An empty collection is returned if there is no result.

```php
foreach ($young_authors as $author) {
    echo $author->name;
}

$names = $young_authors->getColumn('name');

$young_authors_with_z = $young_authors->filter(function ($a) {
    return strpos($a->name, 'z') !== false;
});
```

### SelectOne

By using `one()` or `selectOne()`, the first entity will be returned
instead of the whole collection. Null is returned if there is no
result.

```php
// SELECT * FROM authors LIMIT 1
$author = Author::select($conn)->one()->execute();

// SELECT * FROM authors LIMIT 1
$author = Author::selectOne($conn)->execute();

// SELECT * FROM authors WHERE age < 30 LIMIT 1
$author = Author::selectOne($conn)->where('age', '<', 30)->execute();
```
