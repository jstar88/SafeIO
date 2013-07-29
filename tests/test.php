<?php
	include("../SafeIO.php");
    SafeIO::requireWriteLock("data.txt");
    //
    //  ... Other users can't write data.txt while you are inside here
    //
    echo SafeIO::open("data.txt");
    // now the lock is released
?>