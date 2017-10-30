<?php


namespace Service;


use Commands\Command\CommandProto;

class Checkpoint
{
    /**
     * 
     * @var Pack
     */
    protected $pack;
    
    /**
     * @var CommandProto[]
     */
    protected $commands = [];
    
    /**
     * branch name
     * 
     * @var string
     */
    protected $id;
    
    /**
     * Checkpoint constructor.
     *
     * @param Pack   $pack
     * @param string $id
     */
    public function __construct(Pack $pack, $id)
    {
        $this->pack = $pack;
        $this->id   = $id;
    }
    
    public function getName()
    {
        return $this->id; 
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * @return Pack
     */
    public function getPack()
    {
        return $this->pack;
    }
    
    /**
     * @param Pack $pack
     */
    public function setPack($pack)
    {
        $this->pack = $pack;
    }
    
    /**
     * @return CommandProto[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
    
    /**
     * @param CommandProto[] $commands
     */
    public function setCommands($commands)
    {
        foreach ($commands as $command) {
            $command->getContext()->setCheckpoint($this);
        }
        
        $this->commands = $commands;
    }
    
    public function getBuildPath () 
    {
        $projectName = $this->pack->getProject()->getNameQuoted();
    
        $checkpointName = $this->getName();
        
        $targetPath = 'builds/'.$projectName . '/' . $checkpointName ;
        
        return $targetPath;
    }
}