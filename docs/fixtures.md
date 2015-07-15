# Fixtures

During development and testing it can be useful to pre-load the
database in an automated way. Fixtures make this easy.

## Create a fixture

Implement `ActiveDoctrine\Fixture\FixtureInterface`, which requires
`load()` to actually insert the data and `getTables()` to return an
array of tables the fixture adds data to.

## Running fixtures

Create `ActiveDoctrine\Fixture\FixtureLoader` and add fixtures using
`addFixture()`. Then call `run()`, passing in a
`Doctrine\DBAL\Connection` instance.

Before running the fixtures, all affected tables are emptied.

Thanks to `getTables()`, only the tables affected in the supplied
fixtures are emptied. This is a useful feature if you are adding to an
existing database, of which some tables aren't managed by
ActiveDoctrine.

### Run fixtures in a certain order

Implement `OrderedFixtureInterface` to have fixtures run in order,
lowest number first.

### Append to tables

Pass `true` to the second argument of `run()` to append data to
tables instead of emptying them.
