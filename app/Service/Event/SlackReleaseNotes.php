<?php

namespace Service\Event;

use Admin\App;

class SlackReleaseNotes extends EventProto
{
    public function add($text, $type = null, $data = [])
    {
        if (empty($data[EventConfig::DATA_SLACK])) {
            App::i()->log('SlackReleaseNotes: callback not found, notify with release notes does not send');
            return;
        }

        if (!function_exists('curl_init')) {
            trigger_error('SlackReleaseNotes: curl extension missing');
            return;
        }

        $result = null;
        try {
            $message = [
                'text' => $text,
            ];
            $encode = json_encode($message, JSON_UNESCAPED_UNICODE);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $data[EventConfig::DATA_SLACK]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encode);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($encode),
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            App::i()->log('SlackReleaseNotes: exception on release notes > ' . $e->getMessage());
        } finally {
            App::i()->log('SlackReleaseNotes: response > ' . $result);
        }
    }
}