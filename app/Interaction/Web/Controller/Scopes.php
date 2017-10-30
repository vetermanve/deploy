<?php


namespace Interaction\Web\Controller;


use Interaction\Base\Controller\ControllerProto;

use Service\Data;

class Scopes extends AuthControllerProto
{
    public function indexAction () 
    {
        $data = new Data('', Data::DEFAULT_DATA_DIR, false);
        $scopes = $data->getScopes(Data::DEFAULT_DATA_DIR);
        
        $this->response(['scopes' => $scopes,]);
    }
    
    public function createAction () 
    {
        if ($this->app->request->isPost()) {
            $scopeName = $this->p('name');
            
            if ($scopeName) {
                $scope = new Data($scopeName);
            }
        }
        
        $this->response();
    }
    
    public function editAction () 
    {
        $scopeName = $this->p('scope');
        
        $scope = new Data($scopeName);
        $scope->setReadFrom(__METHOD__);
        
        if ($this->app->request->isPost()) {
            $keys = $this->p('data_key');
            $values = $this->p('data_value');
            
            foreach ($values as &$value) {
                $value = trim($value);
                if ($value && ($value[0] === '[' || $value[0] === '{')) {
                    $decode = json_decode($value, 1);
                    if ($decode !== null) {
                        $value = $decode;
                    }    
                }
            }
            
            $data = $keys && $values ? array_combine($keys, $values) : [];
            $scope->setData($data);
            $scope->write();
        } else {
            $scope->read();
        }
        
        $this->response([
            'scope' => $scopeName,
            'data' => $scope->getData(),
        ]);
    }
    
    public function settingsAction () 
    {
        $scopeName = $this->p('scope');
        $scope = new Data($scopeName, false);
        
        
        if ($this->app->request->isPost()) {
            $action = $this->p('action');
            
            if ($action == 'changeName') {
                $name = $this->p('name');
                if ($name) {
                    $scope->rename($name);
                }
            }
            
            if($action == 'remove') {
                $scope->remove();
            }
        }
    
        $this->response([
            'is_exists' => $scope->isExist(),
            'scope' => $scope->getName(),
            'data' => $scope->getData(),
        ]);
    }
    
}