<?php

namespace Service\Event;

use Admin\App;
use Service\Slot\TagYmlSlot;

class CallbackReleaseNotes extends EventProto
{
    /**
     * @param $text
     * @param null $type
     * @param array $data
     * @return bool|void
     */
    public function add($text, $type = null, $data = [])
    {
        if (empty($data[EventConfig::DATA_CALLBACK])) {
            return;
        }

        $validUri = [];
        $urls = array_unique(array_filter((array) $data[EventConfig::DATA_CALLBACK]));
        foreach ($urls as $uri) {
            $uriWithMessage = TagYmlSlot::mutateCallback($uri, $text);
            if (false !== filter_var($uriWithMessage, FILTER_VALIDATE_URL)) {
                App::i()->log(sprintf('CallbackReleaseNotes: callback `%s` is not valid uri', $uriWithMessage));
                continue;
            }

            $validUri[] = $uriWithMessage;
        }

        if (empty($validUri)) {
            return;
        }

        if (!function_exists('curl_init')) {
            trigger_error('CallbackReleaseNotes: curl extension missing');
            return;
        }

        foreach ($validUri as $uri) {
            $result = null;
            try {
                $ch = curl_init($uri);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                curl_close($ch);
            } catch (\Throwable $e) {
                App::i()->log('CallbackReleaseNotes: exception on release notes > ' . $e->getMessage());
            } finally {
                App::i()->log('CallbackReleaseNotes: response > ' . $result);
            }
        }
    }
}