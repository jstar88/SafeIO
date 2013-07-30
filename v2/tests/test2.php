<?php
$time= time();
require('../ConcurrentFile.php');
 
   if ($_POST) {
  // do stuff
    $file = new ConcurrentFile("data.txt");
    $file->writeLock();
    sleep(20);
    $file->write("hello world old");  
    $file->close();
    
  } 
     
?>
<html>
<head><title>race condition example</title></head>
<body>
  <p>This is a test to see the transaction at work.</p>
  <p>click on the button "do stuff", while page is loading remove the code "sleep(20)" and change "old" to "right", then click in a new tab the same button </p>
  <p>You will see that the second script will wait for the end of first script and the result is right</p>
  <form method="POST">
    Delay caused by lock <?php echo time() - $time; ?> , max is 20.<br>
    <a><?php echo ConcurrentFile::getInstance("data.txt")->read(); ?></a>
    
    <input type="submit" name="do_stuff" value="Do stuff!">
  </form>
</body>
</html>
