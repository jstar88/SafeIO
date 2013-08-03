<?php
/**
 * This class show the effect of concurrance by accessing the same file from different parallel processes.
 * log.txt will be managed by default php functions
 * log2.txt will be managed by CuncurrentFile.php
 * 
 * If you run this file, you will se that log.txt have less contents. 
 * *
*/
error_reporting(E_ALL);
include_once ("../ConcurrentFile.php");

define("FORKS", 5); // number of parallel processes
define("SLEEP",10000); // amount of sleeping time of each process in microseconds

$pid = getmypid();
$path = __FILE__;


//--- multi-process managment
$aCliOpts = getopt('a:');
if ($aCliOpts !== false) //it's a fork
{
    $parent_pid = $aCliOpts['a'];
}
else //no, create some forks
{    
    $parent_pid = "root";
    for ($i = 0; $i < FORKS; $i++)
    {
        execInBackground("php \"$path\" -a $pid");
    }
}

function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /b $cmd", "r")); 
    }
    else {
        exec($cmd . " > /dev/null &");  
    }
} 
//-- end

$content = "pid: $pid <= parent_pid: $parent_pid \n ";
$content2 = file_get_contents("log.txt");
$file = new ConcurrentFile("log2.txt");
$file->writeLock();
$content2s = $file->read();

usleep(10000);

$file->write($content.$content2s)->close();
file_put_contents("log.txt",$content.$content2);



?>