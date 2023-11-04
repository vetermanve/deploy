<?php

declare(strict_types=1);

namespace Exceptions;

class UrlMovedException extends \Exception
{
    protected $message = 'This URL has been moved!';
    protected $code = \Slim\Http\StatusCode::HTTP_MOVED_PERMANENTLY;

    public function __construct($movedToUrlPath = null)
    {
        if ($movedToUrlPath !== null) {
            $currentUrl = (string) \Admin\App::getInstance()->getRequest()->getUri();

            $newUrl = substr(
                $currentUrl,
                0,
                strlen($currentUrl) + 1 - strpos($currentUrl, '/web/'));
            $newUrl = $newUrl . '/' . ltrim($movedToUrlPath, '/');

            $this->message .= " New URL is {$newUrl}";
        }

        parent::__construct($this->message, $this->code);
    }
}
