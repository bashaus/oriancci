---
layout: default
slug  : results
title : Results
---

# Results

Results returned from the database are accessed via a result set which can be manipulated in different ways.

## Collection

Collections are the most common form of result sets. All the information from the information is returned in PHP and able to be access as a PHP array; however the Collection result set type has helper methods to make traversing results simpler.

### Serialization

Collections can be serialised to JSON through the standard PHP function `json_encode()` which returns the result set as a JSON-serialised array.

    $users = User::find();
    echo json_encode($users);

You can also encode a result set to CSV using the `Result->toCSV()` method.

    $users = User::find();
    echo $users->toCSV();

### Relationships

    public function __call($methodName, $methodArguments);

### Simplifying

You can reduce the complexity of a result set using the `Result->collate()` or `Result->each()` method.

The collate method returns as has of a result set.

    // ['1' => 'Adam', '2' => 'Betty', '3' => 'Charlie']
    $users = User::find();
    print_r($users->collate('id', 'firstName'));

The each method returns an index-array of results.

    // [0 => 'Adam', 1 => 'Betty', 2 => 'Charlie']
    $users = User::find();
    print_r($users->each('firstName'));

## Cursor

Cursor result sets are designed for large result data sets. They're best used in unbuffered queries as they allow the database to continue processing while information is being calculated from the database. They also reduce the amount of memory that is required by PHP to parse the database result as only one row is handled at a time.

### Usage

    $users = User::find([])
        ->setResultClass('\Oriancci\Result\Cursor')
        ->select();

    foreach ($users as $user) {
        print_r($user);
    }

### Limitations

Cursors cannot be rewound. Once a row has been retrieved, you cannot return to it later.

You should not rely on the result of `count()` on the result. More information about the value returned from the row count can be found at: [PDOStatement.rowCount](http://uk1.php.net/manual/en/pdostatement.rowcount.php).

> If the last SQL statement executed by the associated PDOStatement was a SELECT statement, some databases 
> may return the number of rows returned by that statement. However, this behaviour is not guaranteed for all 
> databases and should not be relied on for portable applications.