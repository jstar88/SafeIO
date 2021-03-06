<?php

include_once ("../SafeIO.php");
include_once ("Thread.php");

function exception_handler($exception)
{
    file_put_contents("log.txt", "Uncaught exception: " . $exception->getMessage() . "\n");
}

set_exception_handler('exception_handler');

function random_string($length)
{
    $string = "";
    for ($i = 0; $i <= ($length / 32); $i++)
        $string .= md5(time() + rand(0, 99));
    $max_start_index = (32 * $i) - $length;
    $random_string = substr($string, rand(0, $max_start_index), $length);
    return $random_string;
}

function read($u)
{
    return SafeIO::open("data.txt");
}

function write($content)
{
    SafeIO::save("data.txt",$content);
    return $content;
}
function write_slow($content)
{
    SafeIO::startTransaction("data.txt");
    for($i=0;$i<10000;$i++){}
    SafeIO::save("data.txt",$content);
    return $content;
}

/*
****************************************************** 
* Test 1: a writing process followed by a read process.
****************************************************** 
*/

$content = random_string(30);
$content2 = random_string(30);

$thread_a = new Thread("localhost", 80);
$thread_a->setFunc("write", array($content));
$thread_a->start();

$thread_b = new Thread("localhost", 80);
$thread_b->setFunc("read", array(1));
$thread_b->start();


$returnA = $thread_a->getreturn();
$returnB = $thread_b->getreturn();

echo "test 1(simple write):  ";
if ($returnA == $returnB)
    echo "<font color=green>passed</font><br>";
else
    echo "<font color=red>not passed</font><br>";


/*
***********************************************************
* Test 2: a slow writing process followed by a read process.
* The read process should wait untill the write end.
***********************************************************
*/

$thread_c = new Thread("localhost", 80);
$thread_c->setFunc("write_slow", array($content2));
$thread_c->start();

$thread_d = new Thread("localhost", 80);
$thread_d->setFunc("read", array(1));
$thread_d->start();

$returnC = $thread_c->getreturn();
$returnD = $thread_d->getreturn();

echo "test 2(slow writing):  ";
if ($returnC == $returnD)
    echo "<font color=green>passed</font><br>";
else
    echo "<font color=red>not passed</font><br>";

?>