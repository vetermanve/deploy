<?php


namespace Service\Event;


class EventConfig
{
    const EVENT_TYPE_DEPLOY_STATUS  = 'deploy';
    const EVENT_TYPE_RELEASE_STATUS = 'release';
    const EVENT_TYPE_TESTING_STATUS = 'testing';
    const EVENT_TYPE_DEPLOY_END     = 'deploy_end';
    
    const DATA_USER     = '_user';
    const DATA_LOCATION = '_location';
    const DATA_SLOT_NAME = 'slot_name';
    const DATA_BUILD_NAME = 'build_name';
}