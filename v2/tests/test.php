<?php

include("../ConcurrentFile.php");

$file = new ConcurrentFile("data.txt");
$file->write("hello world");
$contents = $file->read();
$file->close();
echo $contents;


$file = new ConcurrentFile("data.txt");
$file->writeLock();
$file->write("hello world");
$contents = $file->read();
$file->releaseLock();
$file->close();
echo $contents;


?>