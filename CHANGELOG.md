Changelog
=========

### 0.1.3 2014-11-03

* Adding Entity#hasRelation().

### 0.1.2 2014-09-26

Small improvements to Entity:

* Ensuring that setValuesRaw() changes the modified fields.
* Adding getFields() static method.
* Adding getRelationDefinitions() static method.

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
