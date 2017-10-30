<?php


namespace Commands\Command;


class EmptyCommand extends CommandProto
{
    private $commandName;
    
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }
    
    public function run()
    {
        $this->runtime->log('Команда '. $this->commandName .'  не найдена ');
    }
    
    public function getId()
    {
        return 'empty';
    }
    
    public function getHumanName()
    {
        return 'Команда не найдена';
    }
    
    /**
     * @return mixed
     */
    public function getCommandName()
    {
        return $this->commandName;
    }
    
    /**
     * @param mixed $commandName
     *
     * @return $this
     */
    public function setCommandName($commandName)
    {
        $this->commandName = $commandName;
        return $this;
    }
}