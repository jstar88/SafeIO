<?php
/**
 * SafeIO
 * Class that read and write content to a file in safe way.
 * @author Covolo Nicola
 * @copyright 2013
 * @license GNU v3
 * @version 2
 */
class ConcurrentFile
{
    const PERMISSIONS = 0664;
    private static $instances = array();
    private $path;
    private $handle;
    private $locks= array();

    /**
     * ConcurrentFile::__construct()
     * Instantiate a new ConcurrentFile object.
     * @param string $path
     * @return ConcurrentFile
     */
    public function __construct($path)
    {
        if (!file_exists($path)) throw new Exception("File not exist at " . $path);
        $this->path = $path;
        $this->handle = fopen($path, "a+");
        if (!$this->handle) throw new Exception("Error while opening the file " . $path);
        $this->locks[] = LOCK_UN;
    }

    /**
     * ConcurrentFile::close()
     * Close the file and destroy the object.
     * @return void
     */
    public function close()
    {
        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        unset(self::$instances[$this->path]);
        chmod($this->path, self::PERMISSIONS);
    }
    /**
     * ConcurrentFile::__destruct()
     * Magic method
     * @return void
     */
    public function __destruct()
    {
        if(isset(self::$instances[$this->path])) $this->close();
    }

    /**
     * ConcurrentFile::write()
     * Save the contents into a file located as the path.
     * Specific in the second argument if the old content should be discarded.
     * @param string $contents
     * @param bool $reset
     * @return ConcurrentFile
     */
    public function write($contents, $reset = true)
    {
        $last = $this->locks[count($this->locks)-1];
        if($last != LOCK_EX) $this->writeLock();  
        
        //--- discard old contents means put the pointer at start ---      
        if($reset)
        {
            ftruncate($this->handle, 0);  
        }
        else
        {
            fseek($this->handle, 0, SEEK_END);
        }
        //--- end ---
        
        //--- safe writing system ---
        $total = 0;
        $len = strlen($contents);
        while ($total < $len && ($written = fwrite($this->handle, $contents))) 
        {
            if($written === false) throw new Exception("Error writing " . $this->path);
            $total += $written;
            $contents = substr($contents, $written);
        }
        fflush($this->handle);
        //--- end ---
               
        if($last != LOCK_EX) $this->releaseLock();
        return $this;
    }

    /**
     * ConcurrentFile::read()
     * Read the content from a file located as the path.
     * @return string
     */
    public function read()
    {
        $last = $this->locks[count($this->locks)-1];
        if($last != LOCK_EX && $last != LOCK_SH) $this->readLock();
        
        //--- moving pointer to the start ---
        rewind($this->handle);
        //--- end ---
        
        //--- safe reading system ---
        $len = filesize($this->path); 
        if($len == 0)
        {
            return "";
        }       
        $contents = '';
        $read = 0;
        while ($read < $len && ($buf = fread($this->handle, $len - $read))) 
        {
            if($buf === false) throw new Exception("Error reading " . $this->path);
            $read += strlen($buf);
            $contents .= $buf;
        }   
        //--- end ---
                
        if($last != LOCK_EX && $last != LOCK_SH) $this->releaseLock(); 
        return $contents;  
    }
    
    /**
     * ConcurrentFile::readLock()
     * Start an shared lock: block others processes write.
     * @return ConcurrentFile
     */
    public function readLock()
    {
        if (!is_readable($this->path)) throw new Exception("File not readable at " . $this->path);
        if (!flock($this->handle, LOCK_SH))  throw new Exception("Error while trying to get lock at " . $this->path);
        $this->locks[] = LOCK_SH;
        return $this;
    }

    /**
     * ConcurrentFile::writeLock()
     * Start an exclusive lock: block others processes from read and write.
     * @return ConcurrentFile
     */
    public function writeLock()
    {
        if (!is_writable($this->path))       throw new Exception("File not writable at " . $this->path);
        if (!flock($this->handle, LOCK_EX))  throw new Exception("Error while trying to get lock at " . $this->path);
        $this->locks[] = LOCK_EX;
        return $this;
    }

    /**
     * ConcurrentFile::releaseLock()
     * Release the previous lock.
     * @return ConcurrentFile
     */
    public function releaseLock()
    {
        $last = array_pop($this->locks);
        if ($last == LOCK_UN) throw new Exception("Wrong explicit usage of locks");
        $last = $this->locks[count($this->locks)-1];
        flock($this->handle, $last);  
        return $this;   
    }

    /**
     * ConcurrentFile::getInstance()
     * Search for active ConcurrentFile objects or instantiate it.
     * @param string $path
     * @return ConcurrentFile
     */
    public static function getInstance($path)
    {
        if (!isset(self::$instances[$path]))
        {
            self::$instances[$path] = new ConcurrentFile($path);
        }
        return self::$instances[$path];
    }
}

?>