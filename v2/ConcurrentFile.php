<?php

class ConcurrentFile
{
    const PERMISSIONS = 0664;
    private static $instances = array();
    private $path;
    private $handle;
    private $locks= array();

    public function __construct($path)
    {
        $this->path = $path;
        $this->handle = fopen($path, "a+");
        if (!$this->handle) throw new Exception("Error while opening the file " . $path);
    }

    public function close()
    {
        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        unset(self::$instances[$this->path]);
        chmod($this->path, self::PERMISSIONS);
    }
    public function __destruct()
    {
        if(isset(self::$instances[$this->path]))
            $this->close();
    }

    public function write($contents, $reset = true)
    {
        $this->writeLock();        
        if ($reset)
        {
            ftruncate($this->handle, 0);  
        }
        else
        {
            fseek($this->handle, 0, SEEK_END);
        }     
        if (fputs($this->handle, $contents) === false) throw new Exception("Error while saving contents at " . $this->path);        
        fflush($this->handle);
        $this->releaseLock();
        return $this;
    }

    public function read()
    {
        if (!file_exists($this->path)) throw new Exception("File not exist at " . $this->path);
        $this->readLock();
        rewind($this->handle);
        $contents = fread($this->handle, filesize($this->path)); 
        $this->releaseLock(); 
        return $contents;  
    }
    
    public function readLock()
    {
        if (!is_readable($this->path)) throw new Exception("File not readable at " . $this->path);
        if (!flock($this->handle, LOCK_SH))  throw new Exception("Error while trying to get lock at " . $this->path);
        $this->locks[] = LOCK_SH;
        return $this;
    }

    public function writeLock()
    {
        if (!is_writable($this->path))       throw new Exception("File not writable at " . $this->path);
        if (!flock($this->handle, LOCK_EX))  throw new Exception("Error while trying to get lock at " . $this->path);
        $this->locks[] = LOCK_EX;
        return $this;
    }

    public function releaseLock()
    {
        $mode = array_pop($this->locks);  
        if ($mode == null)
        {
            $mode = LOCK_UN;    
        }
        flock($this->handle, $mode);  
        return $this;   
    }

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