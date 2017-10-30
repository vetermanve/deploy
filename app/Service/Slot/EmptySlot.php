<?php


namespace Service\Slot;



use Exception;

class EmptySlot extends SlotProto
{
    /**
     * @param $dirName
     *
     * @return mixed
     */
    public function ensureDir($dirName)
    {
        return false;
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
        throw new Exception('NIY');
    }
    
    /**
     * @param $cmd
     * @param $from
     *
     * @return mixed
     */
    public function silentExec($cmd, $from)
    {
        throw new Exception('NIY');
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
        throw new Exception('NIY');
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function rmLink($targetPath, $from)
    {
        throw new Exception('NIY');
    }
    
    public function validate()
    {
        return false;
    }
    
    /**
     * @param $localPath
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     * @throws Exception
     */
    public function deliveryFile($localPath, $targetPath, $from)
    {
        throw new Exception('NIY');
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function createLink($fromPath, $to, $from)
    {
        throw new Exception('NIY');
    }
}