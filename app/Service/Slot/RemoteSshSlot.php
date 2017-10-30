<?php


namespace Service\Slot;


use Admin\App;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SCP;
use phpseclib\Net\SSH2;

class RemoteSshSlot extends SlotProto
{
    /**
     * @var SSH2
     */
    private $ssh;
    
    public function validate()
    {
    
        $this->ssh = new SSH2($this->host);
        if (!$this->ssh->_connect()) {
            $this->state = 'cannot connect to '. $this->host;
        
            return false;
        }
    
        $rsa = new RSA();
        $homeDir = getenv("HOME");
        $rsa->setPrivateKey(file_get_contents($homeDir.'/.ssh/id_rsa'), RSA::PRIVATE_FORMAT_PKCS1);
        $rsa->setPublicKey(file_get_contents($homeDir.'/.ssh/id_rsa.pub'), RSA::PUBLIC_FORMAT_OPENSSH);
        $this->ssh->login('deploy', $rsa);
        
        if (!$this->ssh->isAuthenticated()) {
            $this->state = 'cannot auth for deploy user';
            
            return false;
        }
    
        if (!$this->ensureDir('.')) {
            $this->state = 'Not sure we slot have access to path';
        
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
        $cmd = '[ -d ' . escapeshellcmd($this->path . '/' . $dirName) . ' ]';
        $this->_exec($cmd ,__METHOD__, $result);
        
        if (!$result) {
            $this->silentExec('mkdir '.$dirName, __METHOD__);
            $this->_exec($cmd ,__METHOD__, $result);
            if (!$result) {
                return false;
            }
        }
        
        return true;
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
        $out = $this->_exec($cmd, $from, $res);
        $out = explode("\n", $out);
        $result = $res ? 0 : -1;
        
        return end($out);
    }
    
    /**
     * @param $cmd
     * @param $from
     *
     * @return mixed
     */
    public function silentExec($cmd, $from)
    {
        $this->_exec($cmd, $from, $result);
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
        $this->exec($cmd, $out, $result, $from);
    
        if (count($out) > $outLines) {
            $out = array_merge(
                array_slice($out, 0, floor($outLines / 2)),
                ['...'],
                array_slice($out, -ceil($outLines / 2)));
        }
    
        return [
            'result' => $result !== 0 ? "Fail" : "Success",
            'cmd'    => $cmd,
            'out'    => $out,  
        ];
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function rmLink($targetPath, $from)
    {
        $this->_exec('rm '.$targetPath, $from, $result);
        return $result;
    }
    
    private function _exec($cmd, $from, &$result = null)
    {
        $start = microtime(1);
            
        $out   = $this->ssh->exec('cd '. $this->path .' && '.$cmd.' && echo OKAY || echo FAIL');
        $out = trim($out);
        $result = strpos($out, 'OKAY') !== false;
        App::i()->log('"' . $cmd . '" return: "' . $out . '"',
            __METHOD__ . ' ' . $this->host . ':' . $this->path . ' from ' . $from, $start);
        
        return $out;
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
        $scp = new SCP($this->ssh);
        return $scp->put($this->path.'/'.$targetPath, $localPath, SCP::SOURCE_LOCAL_FILE);
    }
    
    /**
     * @param $targetPath
     * @param $from
     *
     * @return mixed
     */
    public function createLink($fromPath, $to, $from)
    {
        return $this->silentExec('ln -s '. $this->path.'/'.$fromPath.' '.$to, $from);
    }
}