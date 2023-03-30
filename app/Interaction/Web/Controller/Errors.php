<?php

namespace Interaction\Web\Controller;

use Interaction\Base\Controller\ControllerProto;

class Errors extends ControllerProto
{
    public function indexAction ()
    {
        $errorTpl = $this->app->itemId ?? 'unknown';

        $this->response([], $errorTpl);
    }
}
