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
    $reset = true; // true means discard old content inside the file
    SafeIO::save($filePath, $contents , $reset);
```

## More for you: transactions

Transactions are usefull to ensure an atomic behavior. Expecially, in generic applications
, you need to execute actions that require time (like DB queries,loop etc) to know exactly what you need to do:
in this situation a common problem is to keep unchanged the target file from modification of others processes.
It's easier to see the code ;)

Starting a transaction:

```php 
    $filePath = "data.txt";
    SafeIO::startTransaction($filePath);
```

Stopping a transaction and writing the file:

```php 
    $filePath = "data.txt";
    $contents = "some cool data";
    $reset = true;
    SafeIO::stopTransaction($filePath, $contents, $reset );
```
Note: 
* calling function *SafeIO::save* will automatically end and flush the active transaction.
* you can keep active multiple transactions(one per file)


An example:
```php 
    include("../SafeIO.php");
    SafeIO::startTransaction("data.txt","hello world!");
    //<----                                                 
    //  ... Other users can't write data.txt while you are inside here ...
    //<----                                                     
    SafeIO::stopTransaction("data.txt"); // now the lock is released   
    echo SafeIO::open("data.txt");
```





