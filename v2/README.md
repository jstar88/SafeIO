SafeIO v2
======

A class usefull to avoid concurrancy managing files.

## Introduction

#### The problem: cuncurrancy
Concurrency is a property of systems in which several computations are executing simultaneously, and potentially interacting with each other.
Most of time you can see cuncurrancy problem in multithread applications.  
Currently PHP doesn't support multithreading but it allocate one process to each new request.   
Processes can execute the same script and access the same file simultaneously in the hard disk doing writes and reads.  
When this happen it's possible to have a race condition with the result of corrupted or inconsistent file.  

#### PHP solution

PHP provide the [*flock*](http://php.net/manual/en/function.flock.php) function that perform a file locking in different way:
* shared lock (reader)
* exclusive lock (writer)
 
The *flock* function suffer about portability,hard usage and has inadequate documentation.

#### SafeIO coming to help

SafeIO is an utility to open - write contents in files based on PHP *flock* function.  
It simplifies the flock way and add some very usefull features.  
It's not just a wrapper, it's a cuncurrancy manager:  
infact you can make your preferred execution queue blocking others processes from reading and/or writing.

#### I really need this?
Let's do some examples.
Consider the same php script started by 2 different request A and B(each request is a browser click).  
The script is responsible to read a configuration file and update it.  

##### case 1: no precautions
1. The process A is created before B;
2. The process A read the file;
3. B is created;
4. A start to write the file
5. B try to read the file with the effect to stop the writing of A 
6. configuration file is empty or corrupted

##### case 2: flock
1. The process A is created before B;
2. The process A read the file;
3. B is created and read the file;
4. A would start write the file but it see B is reading: it wait;
5. B finished to read, then A start to write;
5. A finished to write, then B start to write. 
6. configuration file is not corrupted BUT process A job was totally useless cause B was the last writer.

##### case 3: SafeIO
1. The process A is created before B;
2. The process A read the file;
3. B is created but SafeIo tell him to wait;
4. A finished to read and write;
5. B read the data updated by A and then write;
5. configuration file is not corrupted and each processes used fresh data. 

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

#### The multitone:
```php 
    $filePath = "data.txt";
    $file = ConcurrentFile::getInstance($filePath);
    $file->close();
```
* the multitone is usefull to reuse the already created object from differents pages.


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

## More for you: locks

Locks are usefull to ensure an atomic behavior. Expecially, in generic applications
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
* inside the write lock, others processes are blocked from read and write;
* usefull to perform a complete list of tasks sequentially;
* preventing other processes from read may decrease performance.

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

#### Final considerations:
* Don't use others php file functions in conjunction with SafeIO: allocating a different handle to files will release locks.  
* SafeIO aim to manage general configurations files: for intensive-parallel load as user's data in a forum, consider to use a database.
* Permission of files managed by SafeIO are automatically setted to 0664 to ensure security.
* I will not implement a new system to prevent concurrancy on multithread server, php developers will improve flock by themselves when servers will be ready.
