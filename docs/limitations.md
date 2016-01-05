# Known limitations

Active Doctrine is designed to be simple and lightweight,
the corollary being it doesn't include every possible ORM feature.

Here is a list of known limitations.
If you find any of these a deal-breaker,
the answer is likely to be "Use Doctrine2 ORM instead".

## It's active record

An inherent limitation of the active record pattern is coupling of
business logic and persistence together.
This violates single responsibility principle and can make business logic harder to test,
since the classes are dependent on the database.

A design decision was made early on to always inject the database connection
and not to rely on a magic way to access a database connection from within entities.
This improves the testing situation slightly because you can create a
mock database connection and inject it into the entity to test,
instead of having to set up a stub database.
