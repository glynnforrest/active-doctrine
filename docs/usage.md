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

## Insert, update and delete

Use `save()` to persist an Entity to the database. A decision will be
made to use an insert or update query automatically, though this can
be overridden by using `insert()` and `update`.

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