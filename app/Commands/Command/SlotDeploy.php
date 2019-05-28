<?php


namespace Commands\Command;


use Commands\CommandConfig;
use Commands\CommandFlow;
use Service\Event\EventConfig;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class SlotDeploy extends DeployCommandProto
{
    /**
     * @var CommandProto[]
     */
    protected $commands = [];
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        if (!$this->context->getSlot()) {
            $this->runtime->log('Слот не назначен');
            return;
        }

        if (!$this->context->getPack()->canUserDeploy()) {
            $this->runtime->log('Вам запрещён этот инструмент деплоя, так как вы не являетесь релиз-инженером. Используйте инструмент "Форкнуть пак", если хотите осуществить деплой');
            return;
        }
        
        $eventTxt = $this->context->getPack()->getName().' на '.$this->context->getSlot()->getName().' | '. $this->context->getPack()->getProject()->getName(false);
        $defaultEventConfig = [
            EventConfig::DATA_CALLBACK => [$this->context->getSlot()->getCallback()],
            EventConfig::DATA_SLACK    => $this->context->getSlot()->getSlack(),
        ];

        $this->runtime->getEventProcessor()->add('🚀 Инициализирована разливка: '.$eventTxt, EventConfig::EVENT_TYPE_DEPLOY_STATUS, $defaultEventConfig);
        $time = microtime(1);
        
        $deployFlow = $this->context->getSlot()->getDeployCommandFlow()->getDeployFlow();
        
        foreach ($deployFlow as $command) {
            $this->runtime->startSection($command->getId(), $command->getHumanName());
            
            $command->setRuntime($this->runtime);
            $command->setContext($this->context);
            $command->prepare();
            $command->run();
        }
    
        $this->runtime->getEventProcessor()->add(
            '🍻 Завершено: '.$eventTxt.' ('.(round(microtime(1) - $time, 1)).' ceк)',
            EventConfig::EVENT_TYPE_DEPLOY_STATUS,
            $defaultEventConfig
        );
        $this->runtime->getEventProcessor()->add(
            '📓 В релиз вошли следующие задачи: ' . implode(', ', (array) $this->getContext()->getPack()->getBranches()),
            EventConfig::EVENT_TYPE_DEPLOY_END, $defaultEventConfig + [
            EventConfig::DATA_SLOT_NAME  => $this->context->getSlot()->getName(),
            EventConfig::DATA_BUILD_NAME => $this->context->getCheckpoint()->getName(),
        ]);
    }
    
    public function getId()
    {
        return CommandConfig::BUILD_AND_ALL_DEPLOY;
    }
    
    public function getHumanName()
    {
        if ($this->context->getSlot()) {
            return 'Разлить на ' . $this->context->getSlot()->getName();
        }
        
        return 'Ошибка: слот не указан';
    }

    /**
     * @return bool
     */
    public function isConfirmRequired()
    {
        return null !== $this->getSlot() ? $this->getSlot()->getConfirm() : parent::isConfirmRequired();
    }

    public function isPrimary()
    {
        return true;
    }

    public function isDanger()
    {
        return null !== $this->getSlot() ? $this->getSlot()->isDanger() : parent::isDanger();
    }
}