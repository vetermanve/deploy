<?php


namespace Service\Slot;


use Service\Util\Fs;

class LocalSlot extends SlotProto
{
    /**
     * @var Fs
     */
    private $adapter;
    
    public function validate()
    {
        $this->adapter = new Fs($this->path);
        
        if (!is_writable($this->path)) {
            $this->state = "Slot target path not writable";
            return false;
        }
        
        $this->state = 'Slot ready';
        return true;
    }
    
    /**
     * @param $dirName
     *
     * @return mixed
     */
    public function ensureDir($dirName)
    {
        return $this->adapter->ensureDir($dirName);
    }
    
    /**
     *
     * @param $cmd
     * @param $out
     * @param $result
     * @param $from
     *
     * @return mixed
     * @throws \Exception
     */
    public function exec($cmd, &$out, &$result, $from)
    {
        return $this->adapter->exec($cmd, $out, $result, $from);
    }
    
    /**
     * @param $cmd
     * @param $from
     *
     * @return mixed
     */
    public function silentExec($cmd, $from)
    {
        return $this->adapter->silentExec($cmd, $from);
    }
    
    /**
     * @param     $cmd
     * @param     $from
     * @param int $outLines
     *
     * @return [
     * 'result' => $result !== 0 ? "Fail" : "Success",
     * 'cmd' => $cmd,
     * 'out' => array_slice($out, 0, $outLines),
     * ];
     * @throws \Exception
     */
    public function stdExec($cmd, $from, $outLines = 10)
    {
        return $this->adapter->stdExec($cmd, $from, $outLines);
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function rmLink($targetPath, $from)
    {
        return $this->adapter->rmLink($targetPath, $from);
    }
    
    /**
     * @param $localPath
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function deliveryFile($localPath, $targetPath, $from)
    {
        return $this->silentExec('cp '.$localPath. ' '.$targetPath, $from);
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function createLink($fromPath, $to, $from)
    {
        return $this->silentExec('ln -s '.$fromPath.' '.$to, $from);
    }
}