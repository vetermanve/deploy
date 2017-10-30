<?php


namespace Service\Util;


use Admin\App;

class Fs
{
    protected $workDir;
    
    /**
     * Fs constructor.
     *
     * @param $workDir
     */
    public function __construct($workDir = null)
    {
        $this->workDir = $workDir ?: getcwd();
    }
    
    /**
     * @return Fs
     */
    public static function i()
    {
        static $i;
        !$i && $i = new self();
        
        return $i;
    }
    
    public function ensureDir($dirName)
    {
        $absolutePath = $this->workDir . '/' . $dirName;
        
        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0777, true);
            chmod($absolutePath, 0777);
        }
        
        return file_exists($absolutePath);
    }
    
    public function hasFile ($fileName) 
    {
        return file_exists($this->workDir.'/'.$fileName);
    }
    
    public function writeFile ($fileName, $fileBody) 
    {
        return file_put_contents($this->workDir.'/'.$fileName, $fileBody);
    }
    
    public function exec($cmd, &$out, &$result, $from, $outLinesLimit = null)
    {
        $out   = [];
        $start = microtime(1);
        try {
            $fp = popen('cd ' . $this->workDir . ' && ' . $cmd . ' 2>&1', "r");
            
            $outString = '';
            while (!feof($fp)) {
                $outString .= fread($fp, 2048);
            }
            $result = pclose($fp);
            
            $outData = explode("\n", $outString);
            
            foreach ($outData as $id => &$line) {
                $line = trim($line);
                if ($line) {
                    $out[] = $line;
                }
            }
            
            if ($outLinesLimit && $out && count($out) > $outLinesLimit) {
                $halfSlice = ($outLinesLimit)/2;
                $out = 
                    array_merge(
                        array_slice($out, 0 , ceil($halfSlice)),
                        ['... skipped '.(count($out) - $outLinesLimit) .' lines ...'],
                        array_slice($out, -floor($halfSlice))
                    );
            }
                
            
            $lastLine = end($out);
            
            $msg = $cmd
                . '  | '
                . ($result !== 0 ? 'Fail: ' . implode(' // ', array_slice($out, 0, 10)) : 'Success')
                . ' | '
                . $this->workDir;
            App::i()->log($msg, $from, $start);
        } catch (\Exception $e) {
            App::i()->log($cmd . ' with exception: ' . $e->getMessage(), $from, $start);
            throw $e;
        }
        
        return $lastLine;
    }
    
    public function silentExec($cmd, $from)
    {
        $this->exec($cmd, $out, $result, $from);
        return $result === 0;
    }
    
    public function stdExec($cmd, $from, $outLinesLimit = 10)
    {
        $start = microtime(1);
        $this->exec($cmd, $out, $result, $from);
    
    
        if ($outLinesLimit && $out && count($out) > $outLinesLimit) {
            $halfSlice = ($outLinesLimit)/2;
            $out =
                array_merge(
                    array_slice($out, 0 , ceil($halfSlice)),
                    ['... skipped '.(count($out) - $outLinesLimit) .' lines ...'],
                    array_slice($out, -floor($halfSlice))
                );
        }
        
        return [
            'result' => $result !== 0 ? "Fail" : "Success",
            'cmd'    => $cmd,
            'out'    => $out,
            'time'   => round(microtime(1) - $start, 4),
        ];
    }
    
    public function rmLink($targetPath, $from)
    {
        $this->exec('rm ' . $targetPath, $out, $res, $from);
        
        return !$res;
    }
    
    /**
     * @return mixed
     */
    public function getWorkDir()
    {
        return $this->workDir;
    }
    
    /**
     * @param mixed $workDir
     */
    public function setWorkDir($workDir)
    {
        $this->workDir = $workDir;
    }
}