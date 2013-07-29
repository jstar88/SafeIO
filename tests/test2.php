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
<head><title>race condition example</title></head>
<body>
  <p>This is a test to see the transaction at work.</p>
  <p>click on the button "do stuff", while page is loading remove the code "sleep(20)" and change "right" to "wrong", then click in a new tab the same button </p>
  <p>You will see that the second script will wait for the end of first script</p>
  <form method="POST">
    Delay caused by lock <?php echo time() - $time; ?> , max is 20.<br>
    <a><?php echo SafeIO::open("data.txt");?></a>
    
    <input type="submit" name="do_stuff" value="Do stuff!">
  </form>
</body>
</html>
