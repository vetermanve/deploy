<?php


namespace Interaction\Web\Controller;


use Interaction\Base\Controller\ControllerProto;

class Build extends AuthControllerProto
{
    
    public function indexAction () 
    {
        return $this->response(['ok']);
    }
    
    public function listAction () 
    {
        $this->response([]);
    }
}