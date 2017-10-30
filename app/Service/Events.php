<?php


namespace Service;


use Admin\App;
use Service\Event\EventConfig;

class Events
{
    /**
     * @var Event\EventProto[]
     */
    private $providers; 
    
    public function add($text, $type = null, $data = []) 
    {
        $app = App::i();
        
        $user = $app->auth->getUserLogin();
        $location = $app->getIdentify();
            
        $data[EventConfig::DATA_USER] = $user;
        $data[EventConfig::DATA_LOCATION] = $location;
    
        foreach ($this->providers as $provider) {
            $provider->add($text, $type, $data);
        }
    }
    
    /**
     * @param Event\EventProto $provider
     */
    public function addProvider($provider)
    {
        $provider->setEventProcessor($this);
        $this->providers[] = $provider;
    }
    
    public function clearProviders () 
    {
        $this->providers = [];
    }
}