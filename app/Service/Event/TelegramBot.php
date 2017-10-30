<?php


namespace Service\Event;


use Admin\App;
use Service\Data;

class TelegramBot extends EventProto
{
    const F_TOKEN = 'token';
    
    private $_botConfig;
    
    public function add($text, $type = null, $data = [])
    {
        $start = microtime(1);
        if (!function_exists('curl_init')) {
            trigger_error('curl extension missing');
            return false;
        };
        
        $token = $this->config()->readCachedIdAndWriteDefault(self::F_TOKEN);
        
        $user = $data[EventConfig::DATA_USER];
        $text = $text."\n".$user.'@'.$data[EventConfig::DATA_LOCATION];
        
        $chat = $this->config()->readCachedIdAndWriteDefault($type);
        if (!$token || !$chat) {
            App::i()->log('Telegram message: '.$text.' not sent for type '. $type, __METHOD__, $start);
            return false;
        }
        
        $ch  = curl_init();
        $url = 'https://api.telegram.org/'.$token.'/sendMessage';
        
        $data = [
            'chat_id'                  => $chat,
            'text'                     => $text,
            'disable_web_page_preview' => true
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $res = curl_exec($ch);
        curl_close($ch);
        
        App::i()->log('Telegram message: "'.$text.'" was sent for type '. $type.' With result: '.$res.' u:'.$url, __METHOD__, $start);
        
        return true;
    }
    
    /**
     * @return Data;
     */
    public function config () 
    {
        if (!$this->_botConfig) {
            $this->_botConfig = new Data('telegram_bot');
        }
        
        return $this->_botConfig;
    }
}