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
    static $handle = array();

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
        if(!isset(self::$handle[$path]))
        {
            self::$handle[$path] = fopen($path, "a+");
        }
        rewind(self::$handle[$path]);
        $handle = self::$handle[$path];
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
     * @return null
     */
    public static function save($content, $path, $reset = true)
    {
        self::requireWriteLock($path);
        $handle = self::$handle[$path];
        // if the file must be cleaned trunk him.
        if ($reset) ftruncate($handle, 0);
        // write the contents.
        if (fputs($handle, $content) === false) throw new Exception("Error while saving contents at " . $path);
        // flush output before releasing the lock
        //fflush(self::$handle);
        // release the lock
        flock($handle, LOCK_UN);
        fclose($handle);
        chmod($path, self::PERMISSIONS);
    }
    
    
    /**
     * SafeIO::requireWriteLock()
     * 
     * @param string $path
     * @return resource file
     */
    public static function requireWriteLock($path)
    {
        if (file_exists($path) && !is_writable($path)) throw new Exception("File not writable at " . $path);
        // Open for writing only; place the file pointer at the end of the file.
        // If the file does not exist, attempt to create it.
        // "w" option is not used to avoid the erase before locking.
        if(!isset(self::$handle[$path]))
        {
            self::$handle[$path] = fopen($path, "a+");
        }
        fseek(self::$handle[$path],0,SEEK_END);
        $handle = self::$handle[$path];
        if (!$handle) throw new Exception("Error while opening the file " . $path);
        // acquire an exclusive lock or wait for it.
        if (!flock($handle, LOCK_EX)) throw new Exception("Error while trying to get lock at " . $path);
    }
}
