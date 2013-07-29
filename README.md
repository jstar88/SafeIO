SafeIO
======

A class usefull to avoid concurrancy managing files.

## Introduction
SafeIO is an utility to open - write contents in files based on PHP *flock* function.
As PHP manual say, multithreading is not supported yet.

## Installation

Download *SafeIO.php* and include it in your scripts:

```php
    require('SafeIO.php');
```


## Basic usage

Retrive data from a file:

```php 
    $filePath = "data.txt";
    SafeIO::open($filePath);
```

Exporting data in a file:

```php 
    $filePath = "data.txt";
    $contents = "some cool data";
    SafeIO::save($filePath, $contents ,$reset);
```

## More for you: transactions

Transactions are usefull to ensure an atomic behavior. Expecially, in generic applications
, you need to execute actions that require time (like DB queries,loop etc) to know exactly what you need to do:
in this situation a common problem is to keep unchanged the target file from modification of others processes.
It's easier to see the code ;)

Starting a transaction:

```php 
    SafeIO::startTransaction("data.txt");
```

Stopping a transaction and writing the file:

```php 
    SafeIO::stopTransaction("data.txt", $contents, $reset );
```
Note: 
* calling function *SafeIO::save* will automatically end and flush the active transaction.
* you can keep active multiple transactions(one per file)





