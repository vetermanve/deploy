<?php


namespace Commands;


use Commands\Command\SlotDeploy;
use Exception;
use Commands\Command\CommandProto;
use Service\Util\Lock;

class CommandRunner
{
    /**
     * @var CommandProto[]
     */
    private $commands;
    
    /**
     * @var CommandContext
     */
    private $context;
    
    protected $commandIdsToRun = [];
    
    /**
     * @var CommandRuntime
     */
    private $runtime;
    
    /**
     * CommandRunner constructor.
     *
     */
    public function __construct()
    {
        $this->runtime = new CommandRuntime();
    }
    
    public function run () 
    {
        $slot = $this->context->getSlot(); 
        if ($slot && !$slot->isValid()) {
            
            $this->runtime->log('Invalid slot for command: ' . $slot->getState());
            return;
        }
    
        $this->context->set(CommandConfig::GLOBAL_WORK_DIR, dirname(getcwd()));
        
        foreach ($this->commandIdsToRun as $commandId) {
            $command = CommandConfig::getCommand($commandId);
            $this->runCommand($command);
        }
    }
    
    public function runCommand (CommandProto $command) 
    {
        try {
            $command->setContext($this->context);
            $command->setRuntime($this->runtime);
            
            $this->runtime->startSection($command->getId(), $command->getHumanName());
            
            $pack = $this->context->getPack();
            if ($pack) {
                $project = $pack->getProject();
                $lock = new Lock('pack_'.$project->getNameQuoted().'_'.$pack->getName(), $command->getHumanName());
                
                if (!$lock->get()) {
                    $this->runtime->log('Pack locked by @'.$lock->getLockData(Lock::OWNER).' for "'
                        .$lock->getLockData(Lock::REASON).'". Lock surely expired after '
                        .($lock->getLockData(Lock::EXPIRE) - time()).'sec.', 'lock');
                    
                    return false;
                }
            }
        
            $command->prepare();
            $command->run();
        
        } catch (Exception $e) {
            $this->runtime->exception($e);
            return false;
        }
        
        isset($lock) && $lock->release();
        
        return true;
    }
    
    /**
     * @return Command\CommandProto[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
    
    /**
     * @param Command\CommandProto[] $commands
     */
    public function setCommands($commands)
    {
        $this->commands = $commands;
    }
    
    /**
     * @return array
     */
    public function getCommandIdsToRun()
    {
        return $this->commandIdsToRun;
    }
    
    /**
     * @param array $commandsOrder
     */
    public function setCommandIdsToRun($commandsOrder)
    {
        $this->commandIdsToRun = $commandsOrder;
    }
    
    /**
     * @return CommandRuntime
     */
    public function getRuntime()
    {
        return $this->runtime;
    }
    
    /**
     * @return CommandContext
     */
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * @param CommandContext $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
    
}