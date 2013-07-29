<?php
	include("../SafeIO.php");
    SafeIO::startTransaction("data.txt");
    //
    //  ... Other users can't write data.txt while you are inside here
    //
    SafeIO::stopTransaction("data.txt");
    echo SafeIO::open("data.txt");
    // now the lock is released
?>