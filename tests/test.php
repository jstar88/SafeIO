<?php
include_once ("SafeIO.php");
include_once ("Thread.php");

function random_string($length) {
	$string = "";
	for ($i = 0; $i <= ($length/32); $i++)
		$string .= md5(time()+rand(0,99));
	$max_start_index = (32*$i)-$length;
	$random_string = substr($string, rand(0, $max_start_index), $length);
	return $random_string;
}

function read($u)
{
    return SafeIO::open("data.txt");
}

function write($content)
{
    SafeIO::save($content, "data.txt");
    return $content;
}
function write_slow($content)
{
    SafeIO::requireWriteLock("data.txt");
    usleep(10000);
    SafeIO::save($content, "data.txt");
    return $content;
}

/*
 ****************************************************** 
 * Test 1: a writing process followed by a read process.
 ****************************************************** 
*/

$content = random_string(60);
$content2 = random_string(60);

$thread_a = new Thread("localhost", 80);
$thread_a->setFunc("write", array($content));
$thread_a->start();

$thread_b = new Thread("localhost", 80);
$thread_b->setFunc("read",array(1));
$thread_b->start();

echo "test 1:  ";
if( $thread_a->getreturn() == $thread_b->getreturn()) echo "<font color=green>passed</font><br>";
else echo "<font color=red>not passed</font><br>";




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
$thread_d->setFunc("read",array(1));
$thread_d->start();

echo "test 2:  ";
if( $thread_c->getreturn() == $thread_d->getreturn()) echo "<font color=green>passed</font><br>";
else echo "<font color=red>not passed</font><br>";



?>