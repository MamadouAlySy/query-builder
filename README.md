# QueryBuilder

A simple php query builder form mysql database

## Requirements

- `php version:` >=8.0

## Basic Usage

```PHP
<?php

require_once './vendor/autoload.php';

queryBuilder = new \MamadouAlySy\QueryBuilder();

$query = $queryBuilder
    ->from('user')
    ->select()
    ->everything()
    ->getQuery();

$sql = $query->getSql();
$parameters = $query->getParameters();
```

`NOTE:` In order to execute the query directely inside the query builder you should pass an object
in the constructor that implement the folowing interface:

`ConnectionInterface.php`:

```PHP
<?php

namespace MamadouAlySy\Interfaces;

interface ConnectionInterface
{
    public function open(): \PDO;
}
```

after that you can use `get, first, commit` methods to execute queries.
- `commit()` execute a query without result
- `first()` execute a query and get on result
- `get()` execute a query and get all result

```PHP
<?php

// Create the user atble
$queryBuilder->create()
    ->table('user')
    ->field('id')->int()->notNull()->autoIncrement()
    ->field('username')->string()->default('mamadou')
    ->commit();

// Getting all users
$users = $queryBuilder
    ->from('user')
    ->select()->everything()
    ->get();
    
// Getting one user
$user = $queryBuilder
    ->from('user')
    ->where('id')->equal(1)
    ->select()->everything()
    ->first();

// Insert a user
$queryBuilder
    ->insert(['username' => 'Mamadou'])->into('user')
    ->commit();
```
