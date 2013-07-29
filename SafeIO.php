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
        $fo = fopen($path, 'r');
        if (!$fo) throw new Exception("Error while opening the file " . $path);
        // acquire a shared lock
        if (flock($fo, LOCK_SH))
        {
            $cts = fread($fo, filesize($path));
            flock($fo, LOCK_UN);
            fclose($fo);
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
        $fp = self::requireWriteLock($path);
        // if the file must be cleaned trunk him.
        if ($reset) ftruncate($fp, 0);
        // write the contents.
        if (fputs($fp, $content) === false) throw new Exception("Error while saving contents at " . $path);
        // flush output before releasing the lock
        fflush($fp);
        // release the lock
        flock($fp, LOCK_UN);
        fclose($fp);
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
        $fp = fopen($path, "a");
        if (!$fp) throw new Exception("Error while opening the file " . $path);
        // acquire an exclusive lock or wait for it.
        if (!flock($fp, LOCK_EX)) throw new Exception("Error while trying to get lock at " . $path);
        return $fp;
    }
}
