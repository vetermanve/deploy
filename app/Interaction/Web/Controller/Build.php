<?php


namespace Interaction\Web\Controller;


use Commands\CommandContext;
use Commands\CommandRunner;
use Interaction\Base\Controller\ControllerProto;
use Service\Data;

class Build extends AuthControllerProto
{
    
    public function indexAction () 
    {
        $data = (new Data('user'))->readCached();

        $command = 'Pack\CheckpointCreateCommand';
        $contextString = 'eyJwYWNrIjoiMTk3NTQ0NDgwMyIsImNoZWNrcG9pbnQiOiJ0ZXN0X3BhY2tfZm9yX2RvdWJsZV9wcm9qZWN0LTIwMjMxMTAxLTE4MTUxOCIsInByb2plY3QiOiIyOTk1ODYwMDE3In0';
//        $contextString = '{"pack":"1975444803","checkpoint":"test_pack_for_double_project-20231101-181518","project":"2995860017"}';
//        var_dump(json_decode($context, true));

        $this->context = new CommandContext();
        $this->context->deserialize($contextString);
        $this->context->set(CommandContext::USER_CONTEXT, null);

//        var_dump($contextString, $this->context->getPack());exit;

        $runner = new CommandRunner();
        $runner->setContext($this->context);
        $runner->setCommandIdsToRun([$command]);

        $runner->run();
//        $this->response([
//            'context' => $runner->getContext(),
//            'runner'  => $runner,
//            'runtime' => $runner->getRuntime(),
//            'packId'  => $this->context->getPack() ? $this->context->getPack()->getId() : '',
//        ], 'apply');

        exit;

        return $this->response(['ok']);
    }
    
    public function listAction () 
    {
        $this->response([]);
    }
}