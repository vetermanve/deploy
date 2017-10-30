<?php

namespace Interaction\Api\Controller;

use Service\Data;

class ServiceConfig extends ApiProto
{
    public function indexAction()
    {
        try {
//            $env = $this->p('env');
            $service = $this->p('service_name');
            $fields  = $this->p('fields', []);
            $all     = $this->p('all', false);
            
            $fieldsIdx = array_flip($fields);
            
            $globalData   = new Data('service_global');
            $globalConfig = $globalData->setReadFrom(__METHOD__)->read();
            
            $serviceConfigName = 'service_' . $service;
            $serviceData       = new Data($serviceConfigName);
            $serviceConfig     = $serviceData->setReadFrom(__METHOD__)->read();
            
            $config = $all 
                ? $serviceConfig + $globalConfig
                : array_intersect_key($serviceConfig + $globalConfig, $fieldsIdx);
        
            if ($fieldsIdx) {
                $requestLog = new Data($serviceConfigName.'_requests');
                $requestLog->setData($fieldsIdx);
                $requestLog->write(false);
            }
            
            $this->response([
                'data'                 => $config,
                'service_config_found' => !empty($serviceConfig),
                'service_config_name'  => $serviceConfigName,
            ]);
                
        } catch (\Exception $e) {
            $this->response([
                'error' => 1,
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}