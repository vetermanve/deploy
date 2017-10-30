<?php


namespace Service\Event;


use Service\Data;
use Service\Util\Lock;

class TestingJenkinsCallback extends EventProto
{
    /**
     * @var Data
     */
    private $configData;
    
    const SAMPLE_URL_PREFIX = 'http://your.jenkins.host/job/deploy_';
    
    /**
     *
     * @param       $text
     * @param null  $type
     *
     * @param array $data
     *
     * @return mixed
     */
    public function add($text, $type = null, $data = [])
    {
        if ($type !== EventConfig::EVENT_TYPE_DEPLOY_END) {
            return ;
        }
        
        $allowedSlots = $this->config()->readCachedIdAndWriteDefault('slots', []);
        $allowedSlotsIdx = array_flip($allowedSlots);
        
        $slotName = $data[EventConfig::DATA_SLOT_NAME];
        if (!isset($allowedSlotsIdx[$slotName]) && !isset($allowedSlotsIdx['*'])) {
            return ;
        }
        
        $lock = new Lock('testing_'.$slotName, ' тестирование в процессе ', 60);
        
        if (!$lock->get()) {
            $this->addEvent('Cборка не отправлена на тест, по причине: '. $lock->getLockeDesc(), EventConfig::EVENT_TYPE_TESTING_STATUS);
            return ;
        }
        
        $hostPrefix = $this->config()->readCachedIdAndWriteDefault('http_path', self::SAMPLE_URL_PREFIX);
        
        if ($hostPrefix === self::SAMPLE_URL_PREFIX) {
            $this->addEvent('Cборка не отправлена на тест, не задан хост.', EventConfig::EVENT_TYPE_TESTING_STATUS);
            return ;
        }
        
        $httpAuthorisation = $this->config()->readCachedIdAndWriteDefault('basic_auth', 'name:password');
        
        $slotId = str_replace('.', '_', trim($slotName, ' .'));
        
        $url = $hostPrefix . $slotId . '/build';
        $cmd = 'curl -k -X POST "' . $url . '" --user '.$httpAuthorisation;
        shell_exec($cmd . ' > /dev/null &');

        $this->addEvent('Cборка '.$data[EventConfig::DATA_BUILD_NAME].' вылитая на '.$slotName." отправлена на автотестирование", EventConfig::EVENT_TYPE_TESTING_STATUS);
    }
    
    /**
     * @return Data;
     */
    private function config ()
    {
        if (!$this->configData) {
            $this->configData = new Data('jenkins');
        }
        
        return $this->configData;
    }
}