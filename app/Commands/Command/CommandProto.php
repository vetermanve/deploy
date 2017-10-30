<?php


namespace Commands\Command;


use Service\Checkpoint;
use Commands\CommandRuntime;
use Commands\CommandContext;
use Service\Node;
use Service\Pack;
use Service\Slot\SlotProto;

abstract class CommandProto
{
    /**
     * @var CommandContext
     */
    protected $context;
    
    /**
     * @var string
     */
    protected $allRoot;
    
    /**
     * @var array
     */
    protected $errors = [];
    
    /**
     * @var CommandRuntime
     */
    protected $runtime;
    
    protected $data;
    
    
    /**
     * BuildProto constructor.
     *
     * @param $allRoot
     */
    public function __construct()
    {
        $this->allRoot = dirname(getcwd());
        $this->allRoot .= '/builds';
    }
    
    abstract public function prepare();
    abstract public function run();
    abstract public function getId();
    abstract public function getHumanName();
    
    /**
     * @return SlotProto
     */
    public function getSlot () 
    {
        return $this->context->getSlot();
    }
    
    /**
     * @return array
     */
    public function getRuntime()
    {
        return $this->runtime;
    }
    
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function isPrimary ()
    {
        return false;
    }
    
    /**
     * @param CommandRuntime $runtime
     */
    public function setRuntime($runtime)
    {
        $this->runtime = $runtime;
    }
    
    /**
     * @return CommandContext
     */
    public function getContext()
    {
        if (!$this->context) {
            $this->context = new CommandContext();
        }
        
        return $this->context;
    }
    
    /**
     * @param CommandContext $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
    
    public function getLink ()
    {
        return '/web/command/?command='.$this->getId().'&context='.$this->getContext()->serialize();
    }
    
    public function isConfirmRequired() 
    {
        return false;
    }
}