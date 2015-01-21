Changelog
=========

### 0.2.1 2015-01-21

* Fixed bug when passing associative arrays to `whereIn()`,
  `andWhereIn()` and `orWhereIn()` in selector classes.
* Adding `unsetRelation()` to Entity. `associateRelation()` calls this
  method if the value given is not an Entity or EntityCollection.
* Adding `getRandom()` and `removeRandom()` to EntityCollection.
* Dramatically speeding up test suite by caching yaml parsing. The
  entire suite now runs in around 1s instead of 3s.

### 0.2.0 2014-12-13

* Adding SqliteSelector and sqlite support.
* Adding support for column types using DBAL type abstraction.
* Adding AbstractRepository for common select queries.
* Adding support for nested where clauses in selectors.
* Changing `getEntitiesChunked()` to `chunk()` in EntityCollection.
* Implementing functional test suite.

### 0.1.4 2014-11-06

* Implementing `count()` methods in Selector classes and
  EntitySelector.
* Adding `setColumnRaw()` to EntityCollection.

### 0.1.3 2014-11-03

* Adding Entity#hasRelation().

### 0.1.2 2014-09-26

Small improvements to Entity:

* Ensuring that `setValuesRaw()` changes the modified fields.
* Adding `getFields()` static method.
* Adding `getRelationDefinitions()` static method.

### 0.1.1 2014-09-03

This release adds new methods to EntityCollection:

* `filter()` returns a new collection with entities filtered using a
  callback.
* `getOne()` finds a single entity where a column matches a certain
  value.
* `remove()` finds a single entity where a column matches a certain
  value and removes it from the collection.
* `getColumnRaw()` gets the values of a column without using getter
  methods.

EntityCollection now implements IteratorAggregate instead of Iterator.

### 0.1.0 2014-09-02

Initial release.

* Entity and EntityCollection classes.
* Selector classes to create select only SQL (currently mysql only).
* An EntitySelector to use a Selector to select entities.
* Three entity relationships (has one, has many, belongs to) with
  support for eagerly loading these relationships.
