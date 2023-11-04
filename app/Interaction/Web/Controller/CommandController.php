<?php


namespace Interaction\Web\Controller;


use Commands\CommandContext;
use Commands\CommandRunner;
use Service\Slot\SlotStack;

class CommandController extends AuthControllerProto
{
    private $title    = '';
    private $subTitle = [];
    
    /**
     * @var CommandContext
     */
    private $context;
    
    public function index()
    {
        $command       = $this->p('command');
        $contextString = $this->p('context');
        $userData      = $this->p('userData');

        $this->context = new CommandContext();
        $this->context->deserialize($contextString);
        $this->context->set(CommandContext::USER_CONTEXT, $userData);
        
//        if (!$this->context->getSlot() && $this->context->getPack()) {
//            $slots = $this->context->getPack()->getProject()->getSlotsPool()->loadProjectSlots()->getSlots();
//            $this->context->setSlot((new SlotStack())->setStack($slots)); 
//        }
        
        $this->_buildTitle();
        
        $runner = new CommandRunner();
        $runner->setContext($this->context);
        $runner->setCommandIdsToRun([$command]);
        
        $this->_runCommands($runner);

        return;
    }
    
    private function _buildTitle()
    {
        if ($this->context->getPack()) {
            $this->_addTitle(__('pack')  . ": {$this->context->getPack()->getName()}");
        }
        
        if ($this->context->getProject()) {
            $this->_addTitle(__('project') . ": {$this->context->getProject()->getName()}");
        }
        
        if ($this->context->getCheckpoint()) {
            $this->_addTitle(__('build') . ": {$this->context->getCheckpoint()->getName()}");
        }

        if ($this->context->getSlot()) {
            $this->_addTitle(__('server') . ": {$this->context->getSlot()->getDescription()}");
        }
    }
    
    private function _addTitle($text)
    {
        if (!$this->title) {
            $this->title = $text;
            $this->setTitle($text);
            
            return $this->title;
        }
        
        $this->subTitle[] = $text;
        $this->setSubTitle(implode('<br>', $this->subTitle));
    }
    
    
    /**
     * @param $runner CommandRunner
     */
    private function _runCommands($runner)
    {
        $runner->run();
        $this->response([
            'context' => $runner->getContext(),
            'runner'  => $runner,
            'runtime' => $runner->getRuntime(),
            'packId'  => $this->context->getPack() ? $this->context->getPack()->getId() : '',
        ], 'apply');
    }
}