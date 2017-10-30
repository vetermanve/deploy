<?php


namespace Commands;


use Service\Event\TelegramBot;
use Service\Event\TestingJenkinsCallback;
use Service\Events;

class CommandRuntime implements \ArrayAccess
{
    private $data = [];
    
    private $logs        = [];
    private $currentSection;
    private $currentSectionStartTime;
    private $sectionNames;
    private $coreSection = 0;
    private $errors      = [];
    private $exceptions  = [];
    
    /**
     * @var Events
     */
    private $eventProcessor;
    
    /**
     * CommandRuntime constructor.
     *
     */
    public function __construct()
    {
        $this->eventProcessor = new Events();
        $this->eventProcessor->addProvider(new TelegramBot());
        $this->eventProcessor->addProvider(new TestingJenkinsCallback());
    }
    
    
    public function startSection($id, $name)
    {
        if ($this->currentSection !== $id && $this->currentSectionStartTime && $this->logs[$this->currentSection]) {
            $time = round(microtime(1) - $this->currentSectionStartTime, 4);
            $this->logs[$this->currentSection]['time'] = $time;
        }
        
        $this->currentSection = $id;
        $this->currentSectionStartTime = microtime(1);
        $this->sectionNames[$id] = $name;
        if (!isset($this->logs[$this->currentSection])) {
            $this->logs[$this->currentSection] = [];
        }
    }
    
    private function _checkSection() {
        if (!$this->currentSection) {
            $this->startSection('core_' . (++$this->coreSection), 'Логи ядра #' . $this->coreSection);
        }   
    }
    
    public function log($data, $key = null)
    {
        $this->_checkSection();
        
        if ($key === null) {
            $this->logs[$this->currentSection][] = $data;
        } else {
            if (isset($this->logs[$this->currentSection][$key])) {
                $this->logs[$this->currentSection][$key] = array_merge((array)$this->logs[$this->currentSection][$key], (array)$data);
            } else {
                $this->logs[$this->currentSection][$key] = $data;    
            }
        }
    }
    
    public function error($data)
    {
        $this->_checkSection();
        
        if (!isset($this->errors[$this->currentSection])) {
            $this->errors[$this->currentSection] = [];
        }
        
        $this->errors[$this->currentSection][] = $data;
        $this->log($data);
    }
    
    public function exception(\Exception $exception)
    {
        $this->_checkSection();
        
        if (!isset($this->exceptions[$this->currentSection])) {
            $this->exceptions[$this->currentSection] = [];
        }
        
        $this->exceptions[$this->currentSection][] = $exception;
        $this->log($exception->getMessage());
    }
    
    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }
 
    public function getSectionName($id) 
    {
        return $this->sectionNames[$id];
    }
    
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
    
    /**
     * @param      $key
     * @param null $default
     *
     * @return array
     */
    public function getData($key, $default = null)
    {
        return isseT($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    /**
     * @param       $key
     * @param array $data
     */
    public function setData($key, $data)
    {
        $this->data[$key] = $data;
    }
    
    public function offsetExists($offset)
    {
        return isset($this->data[$this->currentSection][$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->data[$this->currentSection][$offset]) ? $this->data[$this->currentSection][$offset] : null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->log($value, $offset);
        return true;
    }
    
    public function offsetUnset($offset)
    {
        unset($this->data[$this->currentSection][$offset]);
    }
    
    /**
     * @return Events
     */
    public function getEventProcessor()
    {
        return $this->eventProcessor;
    }
}