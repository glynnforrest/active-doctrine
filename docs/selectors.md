# Selectors

## Selecting by relation

`EntitySelector` supports simple mapping of entity relationships in where queries.

```php
$author = Author::selectOne($connection)
    ->where('name', 'Charles Dickens')
    ->execute();
$books = Book::select($connection)
    ->with('author')
    ->where('id', '<', 30)
    ->orWhere('author', $author)
    ->execute();

//SELECT * FROM `books` WHERE `id` < ? OR `authors_id` = ?
```
