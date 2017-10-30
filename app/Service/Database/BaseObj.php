<?php


namespace Service\Database;


class BaseObj
{
    private $scope;
    private $id;
    private $data;
    
    /**
     * BaseObj constructor.
     *
     * @param $scope
     * @param $id
     */
    public function __construct($scope, $id)
    {
        $this->scope = $scope;
        $this->id    = $id;
        $this->refresh();
    }
    
    public function set($key, $value) 
    {
        $this->data[$key] = $value;   
        return $this;
    }
    
    public function subSet ($key, $subKey, $value) 
    {
        $this->data[$key][$subKey] = $value;
        return $this;
    }
    
    public function get ($key, $default = null)  
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    public function subGet ($key, $subKey, $default = null) 
    {
        return isset($this->data[$key][$subKey]) ? $this->data[$key][$subKey] : $default;
    }
    
    public function refresh () 
    {
        $this->data = JBase::i()->readObj($this->scope, $this->id, []);
    }
    
    public function write () 
    {
        return JBase::i()->writeObj($this->scope, $this->id, $this->data);
    }
}