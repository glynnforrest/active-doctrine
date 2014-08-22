# Creating Entities

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
