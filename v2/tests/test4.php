<?php
/**
 * This example aiming to test the behavior of CuncurrentFile.php under intensive load
*/
include ("../ConcurrentFile.php");

function random_string($length)
{
    $string = "";
    for ($i = 0; $i <= ($length / 32); $i++)
        $string .= md5(time() + rand(0, 99));
    $max_start_index = (32 * $i) - $length;
    $random_string = substr($string, rand(0, $max_start_index), $length);
    return $random_string;

}

function test($content, $n)
{
    for ($i = 0; $i < $n; $i++)
    {
        $file = new ConcurrentFile("data.txt");
        $file->writeLock();
        $file->write($content);
        $file->write($content, false);
        $contents = $file->read();
        $file->close();

        if ($contents === ($content . $content))
        {
            echo "ok<br>";
        }
        else
        {
            echo "not ok<br>";
        }
    }
}

test(random_string(1000000) , 10);

?>