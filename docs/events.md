# Events

Events can be triggered at various points in the entity
life cycle. This can be used to update fields automatically at a
certain time.

## Listen for an event

Use `::addEventCallback()` to add an event listener function. This
adds a callback for every instance of the specified class when the
given event is called. Functions will be passed the entity that the
event has been triggered on.

```php
Book::addEventCallback('my-event', function($book) {
    $book->set('synopsis', 'My custom event');
})
```

To call the event, use `callEvent()`.

```php
$book->callEvent('my-event');
//callback function called, passing in $book as an argument
```

## Built in events

Some events are triggered inside entity classes automatically:

### insert

Called just before an entity is inserted into the database. This event
could be used to set fields on the database automatically, e.g. a
time stamp.

### update

Called just before an entity is updated in the database. This event is
called regardless of whether an update will actually take place (there
are any changes to the values in the database).

## Traits

ActiveDoctrine contains some built in traits for reusable
functionality using events. See `ActiveDoctrine\Entity\Traits`.
