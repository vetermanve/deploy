<?php


namespace Service\Event;


use Service\Events;

abstract class EventProto
{
    /**
     * @var Events
     */
    private $eventProcessor;
    
    /**
     *
     * @param       $text
     * @param null  $type
     *
     * @param array $data
     *
     * @return bool
     */
    abstract public function add($text, $type = null, $data = []);
    
    /**
     * @param Events $eventProcessor
     */
    public function setEventProcessor($eventProcessor)
    {
        $this->eventProcessor = $eventProcessor;
    }
    
    protected function addEvent ($text, $type = null, $data = []) 
    {
        $this->eventProcessor && $this->eventProcessor->add($text, $type, $data);   
    }
}