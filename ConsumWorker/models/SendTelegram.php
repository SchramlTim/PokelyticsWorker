<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 11.09.2017
 * Time: 18:31
 */

namespace ConsumWorker;

use React\EventLoop\StreamSelectLoop;
use Bunny\Async\Client;
use Bunny\Channel;

include_once __DIR__ .'/../interfaces/WorkerInterface.php';

class SendTelegram implements WorkerInterface
{

    public function work(StreamSelectLoop $loop, array $aMessage)
    {
        $aLocation = $aMessage['location'];
        $aTelegramMessages = array_keys($aLocation['telegram_messages']);

        foreach($aTelegramMessages as $aChatMessage){
            $aParam = [
                'caption' => 'Test',
                'chat_id' => $aMessage['location']['chat_id'],
                'photo' => POKELYTICS_IMAGE_LOCATION.$aTelegramMessages['pokemon_info'].'.png'
            ];
        }
    }
}