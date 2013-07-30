SafeIO v2
======

A class usefull to avoid concurrancy managing files.

## Introduction
SafeIO is an utility to open - write contents in files based on PHP *flock* function.
As PHP manual say, multithreading is not supported yet.

## Installation

Download *ConcurrentFile.php* and include it in your scripts:

```php
    require('ConcurrentFile.php');
```


## Basic usage

#### The ConcurrentFile object:

```php 
    $filePath = "data.txt";
    $file = new ConcurrentFile($filePath);
    $file->close();
```
* remember to close the file;
* you can use unset($file) instead close();


#### Retrive data from a file:

```php 
    $filePath = "data.txt";
    $file = new ConcurrentFile($filePath);
    $contents = $file->read();
    $file->close();
    echo $contents;
```
* read() is always atomic and safe.

#### Exporting data in a file:

```php 
    $filePath = "data.txt";
    $contents = "hello world!";
    $reset = true;
    $file = new ConcurrentFile($filePath);
    $file->write($contents, $reset);
    $file->close();
```
* $reset is true by default: it means to discard the old contents;
* write() is always atomic and safe.

## More for you: transactions

Transactions are usefull to ensure an atomic behavior. Expecially, in generic applications
, you need to execute actions that require time (like DB queries,loop etc) to know exactly what you need to do:
in this situation a common problem is to keep unchanged the target file from modification of others processes.
It's easier to see the code ;)

#### Write lock:

```php 
    $filePath = "data.txt";
    $file = new ConcurrentFile($filePath);
    $file->writeLock();
    // do some stuff
    $file->releaseLock();
    $file->close();
```
* close() will automatically call releaseLock() if needed;
* inside the write lock you can also read and write whenever as you want;
* inside the write lock, others processes are blocked from read and write.

#### Read lock:

```php 
    $filePath = "data.txt";
    $file = new ConcurrentFile($filePath);
    $file->readLock();
    // do some stuff
    $file->releaseLock();
    $file->close();
```
* close() will automatically call releaseLock() if needed;
* inside the read lock you can also read and write whenever as you want;
* inside the read lock, others processes are blocked from write.

#### Nested lock:

```php 
    $filePath = "data.txt";
    $file = new ConcurrentFile($filePath);
    $file->readLock();
    // do some stuff
      $file->writeLock()
       // do others stuff
      $file->releaseLock();
    //
    $file->releaseLock();
    $file->close();
```



#### An example:
In this example another process must wait for the end of *read and write* of this one.
```php 
    $file = new ConcurrentFile("data.txt");
    $file->writeLock();
    $file->write("hello world");
    $contents = $file->read();
    $file->releaseLock();
    $file->close();
    echo $contents;
```
