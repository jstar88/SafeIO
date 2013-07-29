<?php

/**
 * SafeIO
 * Class that read and write content to a file
 * @author Covolo Nicola
 * @copyright 2013
 * @license GNU v3
 */
class SafeIO
{
    const PERMISSIONS = 0664;
    private static $handle = array();

    /**
     * SafeIO::open()
     * Read the content from a file located as the path.
     * @param string $path
     * @return string 
     */
    public static function open($path)
    {
        if (!file_exists($path)) throw new Exception("file not exist " . $path);
        if (!is_readable($path)) throw new Exception("file not readable " . $path);
        // Open for reading only; place the file pointer at the beginning of the file.
        //rewind(self::$handle[$path]);
        $handle = fopen($path, "r");
        if (!$handle) throw new Exception("Error while opening the file " . $path);
        // acquire a shared lock
        if (flock($handle, LOCK_SH))
        {
            $cts = fread($handle, filesize($path));
            flock($handle, LOCK_UN);
            fclose($handle);
            chmod($path, self::PERMISSIONS);
            return $cts;
        }
        else throw new Exception("Error while trying to get lock at " . $path);
    }


    /**
     * SafeIO::save()
     * Save the content into a file located as the path.
     * @param string $content
     * @param string $path
     * @param boolean $reset
     * @return void
     */
    public static function save($path, $content, $reset = true)
    {
        self::startTransaction($path);
        self::stopTransaction($path, $content, $reset);
    }


    /**
     * SafeIO::startTransaction()
     * Start a transaction where nobody except you can write the file.
     * 
     * @param string $path
     * @return void
     */
    public static function startTransaction($path)
    {
        if (file_exists($path) && !is_writable($path)) throw new Exception("File not writable at " . $path);
        // Open for writing only; place the file pointer at the end of the file.
        // If the file does not exist, attempt to create it.
        // "w" option is not used to avoid the erase before locking.
        if (!isset(self::$handle[$path]))
        {
            self::$handle[$path] = fopen($path, "a");
        }
        $handle = self::$handle[$path];
        if (!$handle) throw new Exception("Error while opening the file " . $path);
        // acquire an exclusive lock or wait for it.
        if (!flock($handle, LOCK_EX)) throw new Exception("Error while trying to get lock at " . $path);
    }
    
    
    /**
     * SafeIO::stopTransaction()
     * 
     * Stop the current transaction and write the file.
     * @param string $path
     * @param string $content
     * @param bool $reset
     * @return void
     */
    public static function stopTransaction($path, $content = "", $reset = false)
    {
        if (file_exists($path) && !is_writable($path)) throw new Exception("File not writable at " . $path);
        if (!isset(self::$handle[$path])) throw new Exception("Transaction not started at file " . $path);
        $handle = self::$handle[$path];
        // if the file must be cleaned trunk him.
        if ($reset) ftruncate($handle, 0);
        // write the contents.
        if (fputs($handle, $content) === false) throw new Exception("Error while saving contents at " . $path);
        // flush output before releasing the lock
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        unset(self::$handle[$path]["w"]);
        chmod($path, self::PERMISSIONS);
    }
}
