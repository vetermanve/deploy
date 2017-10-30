<?php


namespace Service\Util;


use Admin\App;

/**
 * Class Lock
 * 
 * Non atomic logic locks
 * 
 * @package Service\Util
 */
class Lock
{
    private $name;
    private $owner;
    private $reason = '';
    private $ttl    = 120;
    
    private $lockData = [];
    
    const DIR = 'locks';
    
    const OWNER  = 'owner';
    const EXPIRE = 'expire';
    const DATE   = 'date';
    const TIME   = 'time';
    const TTL    = 'ttl';
    const REASON = 'reason';
    
    
    /**
     * Lock constructor.
     *
     * @param        $name
     * @param string $reason
     * @param int    $ttl
     */
    public function __construct($name, $reason = '', $ttl = 120)
    {
        Fs::i()->ensureDir('locks');
        $this->name   = preg_replace('/\W+/', '_', $name);
        $this->owner  = App::i()->auth->getUserLogin();
        $this->ttl    = $ttl;
        $this->reason = $reason;
    }
    
    public function getLockFile()
    {
        
        return 'locks/' . escapeshellcmd($this->name) . '.lock';
    }
    
    public function get()
    {
        $file = $this->getLockFile();
        if (!file_exists($file)) {
            return $this->writeLock();
        }
        
        $this->lockData = @file_get_contents($file);
        if (!$this->lockData) {
            return $this->writeLock();
        }
        
        $this->lockData = json_decode($this->lockData, 1);
        if (!$this->lockData) {
            return $this->writeLock();
        }
        
        if ($this->lockData['expire'] < time()) {
            return $this->writeLock();
        }
        
        return false;
    }
    
    public function writeLock()
    {
        $time = time();
        $lock = [
            self::OWNER  => $this->owner,
            self::EXPIRE => $time + $this->ttl,
            self::DATE   => date('c', $time),
            self::TIME   => $time,
            self::TTL    => $this->ttl,
            self::REASON => $this->reason,
        ];
        
        return (bool)file_put_contents($this->getLockFile(), json_encode($lock, JSON_PRETTY_PRINT));
    }
    
    public function getLockeDesc()
    {
        return isset($this->lockData['owner']) ? $this->lockData['owner'] . ' for ' . $this->lockData['reason']
            : 'not loaded';
    }
    
    public function getLockData($key, $default = null)
    {
        return isset($this->lockData[$key]) ? $this->lockData[$key] : $default;
    }
    
    public function release()
    {
        return unlink($this->getLockFile());
    }
}