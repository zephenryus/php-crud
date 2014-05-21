# PHP CRUD
A PHP framework which offers easy access to create, read, update and delete records of a MySQL database. The purpose of this class is to create a public interface with a MySQL database which is more intuitive and PHP programming centric. Rather than submitting SQL queries to the database, associative arrays containing values are passed to the methods which are then parsed into create, read, update and delete queries.

## Usage

CRUD is separated into 4 methods: Create, Read, Update and Delete.

### Create

#### Method

```php
bool crud::create( string $table, array $arr )
```

Where:

* `$table` is the name of the table to insert the values into

* `$arr` is an associative array of keys and values to insert into the table

#### Description

Creates and executes an INSERT SQL query from an associative array The associative array submitted to this method should follow a naming convention of key => value where key is the column name into which the value would be inserted:

```php
Array (
    'firstName' => 'Foo',
    'lastName' => 'Bar',
    'department' => 'Foobar'
)
```

Would yield the following result (or something similar):


| employee_id | firstName | lastName | department |
|-------------|-----------|----------|------------|
| 859648      | Foo       | Bar      | Foobar     |

The method is designed to recursively process multidimensional arrays:

```php
Array (
    [0] => Array (
        'firstName' => 'Foo',
        'lastName' => 'Bar',
        'department' => 'Foobar'
    )
    [1] => Array (
        'firstName' => 'Fooy',
        'lastName' => 'Barington',
        'department' => 'Sales'
    )
)
```

Would yield:

| employee_id | firstName | lastName  | department |
|-------------|-----------|-----------|------------|
| 859649      | Foo       | Bar       | Foobar     |
| 859650      | Fooy      | Barington | Sales      |

And would return an array of return values (i.e. `Array( true, true )`).

### Read

#### Method

```php
array crud::read( string $query )
```

Where

* `$query` is a SQL query to submit to database

#### Description

Creates and executes a SELECT query on a specified table of a database The query is performed on the current database and results are returned as a multidimensional array. The format for a returned row is as follows. The table:

| employee_id | firstName | lastName  | department |
|-------------|-----------|-----------|------------|
| 859649      | Foo       | Bar       | Foobar     |
| 859650      | Fooy      | Barington | Sales      |

would yield:

```php
Array (
    [0] => Array (
        'firstName' => 'Foo',
        'lastName' => 'Bar',
        'department' => 'Foobar'
    )
    [1] => Array (
        'firstName' => 'Fooy',
        'lastName' => 'Barington',
        'department' => 'Sales'
    )
)
```

It should be noted (for traversing purposes) if only 1 row is requested the result will still be returned as a _multidimensional array_. If the query:

```sql
SELECT * FROM employees LIMIT 1
```

were performed on the above table the resulting associative array would be:

```php
Array (
    [0] => Array (
        'firstName' => 'Foo',
        'lastName' => 'Bar',
        'department' => 'Foobar'
    )
)
```

### Update

#### Method

```php
bool update( string $table, array $arr, string $id, [ mixed $primaryKey = '' ] )
```

Where:

* `$table` is the name of table to remove record from
* `$arr` is an ssociative array of keys and values to update
* `$id` is the id of record to update
* `$primaryKey` is the name of column of table which contains the primary key

#### Description

Creates and executes an UPDATE SQL query on a specified row of a database This method performs very similarly to the create method. The associative array submitted to this method should follow a naming convention of key => value where key is the column name of which the value would be updated:

```php
Array (
   'firstName' => 'Foo',
   'lastName' => 'Bar',
   'department' => 'Foobar'
)
```

Would yield the following result (or something similar):

| employee_id | firstName | lastName | department |
|-------------|-----------|----------|------------|
| 859648      | Foo       | Bar      | Foobar     |

The method is designed to recursively process multidimensional arrays:

```php
Array (
    [0] => Array (
        'firstName' => 'Foo',
        'lastName' => 'Bar',
        'department' => 'Foobar'
    )
    [1] => Array (
        'firstName' => 'Fooy',
        'lastName' => 'Barington',
        'department' => 'Sales'
    )
)
```

Would yield:

| employee_id | firstName | lastName  | department |
|-------------|-----------|-----------|------------|
| 859649      | Foo       | Bar       | Foobar     |
| 859650      | Fooy      | Barington | Sales      |

And would return an array of return values (i.e. Array( true, true )).

### Delete

#### Method

```php
int crud::delete( string $table, string $id [, string $primaryKey] )
```

Where:

* `$table` is the name of table to remove record from
* `$id` is the id of record to remove
* `$primaryKey` is the name of column of table which contains the primary key

#### Description

Creates and executes a DELETE SQL query on a specified row of a database

The id value submitted to the method is checked against the table's primary key. The primary key is optional and, if not passed, the method will attempt to find the primary key of the table.

