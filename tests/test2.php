<?php
$time= time();
require('../SafeIO.php');
 
   if ($_POST) {
  // do stuff
    SafeIO::startTransaction("data.txt");
    sleep(20);
    SafeIO::save( "data.txt","hello world right");  
    
  } 
     
?>
<html>
<head><title>session race condition example</title></head>
<body>
  <form method="POST">
    Delay caused by lock <?php echo time() - $time; ?> , max is 5.<br>
    <a><?php echo SafeIO::open("data.txt");?></a>
    
    <input type="submit" name="do_stuff" value="Do stuff!">
  </form>
</body>
</html>