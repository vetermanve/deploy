<?php


namespace Service\Database;


use Service\Data;

class JBase
{
    const ROOT_DIR = 'db';
    
    /**
     * @return JBase
     */
    public static function i()
    {
        static $i;
        !$i && $i = new self();
        return $i;
    }
    
    public function readKey($scope, $id, $key, $default = null)
    {
        $data = $this->getDataObj($scope, $id)->setReadFrom(__METHOD__)->readCached();
        return isset($data[$key]) ? $data[$key] : $default;
    }
    
    public function readObj($scope, $id, $default = null)
    {
        $data = $scope && $id ? $this->getDataObj($scope, $id)->setReadFrom(__METHOD__)->read() : null;    
        return $data ? $data : $default;
    }
    
    public function writeKey($scope, $id, $key, $value)
    {
        $data = $this->getDataObj($scope, $id);
        $data->setReadFrom(__METHOD__);
        $data->read();
        $data->setData([$key => $value] + $data->getData());
        return $data->write();
    }
    
    public function writeObj($scope, $id, $update = [])
    {
        $data = $this->getDataObj($scope, $id);
        $data->setReadFrom(__METHOD__);
        $data->setData($update);
        return $data->write(false);
    }
    
    /**
     * @param $scope
     * @param $id
     *
     * @return Data
     */
    private function getDataObj ($scope, $id) {
        return (new Data($id, self::ROOT_DIR.'/'.$scope))->setReadFrom(__METHOD__);
    }
}