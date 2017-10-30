<?php


namespace Interaction\Api\Controller;


use Interaction\Base\Controller\ControllerProto;

class ApiProto extends ControllerProto
{
    public function before()
    {
        parent::before();
        $this->isJson = true;
    }
    
    public function error ($data) 
    {
        $this->response(['error' => $data]);
        return false;
    }
}