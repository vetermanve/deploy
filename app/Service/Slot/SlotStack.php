<?php


namespace Service\Slot;


class SlotStack extends SlotProto   
{
    /**
     * @var SlotProto[]
     */
    private $stack = [];
    
    public function validate()
    {
        $res = true;
        
        foreach ($this->stack as $slot) {
            $res = $res && $slot->validate();
        }
        
        return $res;
    }
    
    /**
     * @param $dirName
     *
     * @return bool
     */
    public function ensureDir($dirName)
    {
        $res = true;
    
        foreach ($this->stack as $slot) {
            $res = $res && $slot->ensureDir($dirName);
        }
    
        return $res;
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
        $allOut = [];
        $result = true;
        foreach ($this->stack as $id => $slot) {
            $allOut[$id] = '';
            $result && $slot->exec($cmd, $allOut[$id], $result, __METHOD__);       
        }
        
        return '';
    }
    
    /**
     * @param $cmd
     * @param $from
     *
     * @return mixed
     */
    public function silentExec($cmd, $from)
    {
        $result = true;
        foreach ($this->stack as $id => $slot) {
            $allOut[$id] = '';
            $result = $result && $slot->silentExec($cmd, __METHOD__);
        }
        
        return $result;
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
        $result = [
            'result' => [],
            'cmd' => [],
            'out' => [],
        ];
        
        foreach ($this->stack as $id => $slot) {
            $allOut[$id] = '';
            $resultItem = $slot->stdExec($cmd, __METHOD__, $outLines);
            $result['result'][$slot->getName()] = $resultItem['result']; 
            $result['cmd'][$slot->getName()] = $resultItem['cmd']; 
            $result['out'][$slot->getName()] = $resultItem['out']; 
        }
    
        return $result;
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function rmLink($targetPath, $from)
    {
        $result = true;
        foreach ($this->stack as $id => $slot) {
            $result = $result && $slot->rmLink($targetPath, $from);
        }
    
        return $result;
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
        $result = true;
        foreach ($this->stack as $id => $slot) {
            $result = $result && $slot->deliveryFile($localPath, $targetPath, $from);
        }
    
        return $result;
}
    
    /**
     * @return SlotProto[]
     */
    public function getStack()
    {
        return $this->stack;
    }
    
    /**
     * @param SlotProto[] $stack
     *
     * @return $this
     */
    public function setStack($stack)
    {
        $this->stack = $stack;
        return $this;
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function createLink($fromPath, $to, $from)
    {
        $result = true;
        foreach ($this->stack as $id => $slot) {
            $result = $result && $slot->createLink($fromPath, $to, $from);
        }
    
        return $result;
    }
}