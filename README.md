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


## Usage

Retrive data from a file:

```php 
    SafeIO::open("data.txt");
```

Exporting data in a file:

```php 
    SafeIO::save($content, "data.txt");
```

Request a lock for near-future write

```php 
    SafeIO::requireWriteLock("data.txt");
```




