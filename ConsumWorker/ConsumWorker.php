<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 11.09.2017
 * Time: 17:55
 */

require __DIR__ . '/../vendor/autoload.php';

use Bunny\Channel;
use Bunny\Async\Client;
use Bunny\Message;
use ConsumWorker\Location;
use ConsumWorker\GoMapConnector;

include_once __DIR__ .'/../config.php';
include_once __DIR__ .'/models/Location.php';
include_once __DIR__ . '/models/NotifyTopSpawns.php';
include_once __DIR__ .'/models/GoMapConnector.php';
include_once __DIR__ .'/models/GoogleMapsConnector.php';
include_once __DIR__ .'/models/TelegramConnector.php';
include_once __DIR__ . '/models/SendMessageToTelegram.php';
include_once __DIR__ . '/models/AnalyzeGyms.php';
include_once __DIR__ . '/models/AnalyzeSpawns.php';

$loop = React\EventLoop\Factory::create();

$oClient = new Client($loop);

$oChannel = $oClient->connect()->then(
    function (Client $client) {
        return $client->channel();
    }
)->then(
    function (Channel $channel) {
        return $channel->qos(0, 1)->then(
            function () use ($channel) {
                return $channel;
            }
        );
    }
)->then(
    function (Channel $channel) {
        return $channel->queueDeclare(BUNNY_WORKER_CHANNEL_NAME, false, false, false, false)->then(
            function () use ($channel) {
                return $channel;
            },
            function ($e){
                echo $e->getMessage();
            }
        );
    }
);

$oChannel->then(
    function (Channel $channel) use (&$loop) {
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
        $channel->consume(
            function (Message $message, Channel $channel, Client $client) use (&$loop) {
                $aMessage = json_decode($message->content,true);
                $sClassNamespace = 'ConsumWorker\\'.$aMessage['className'];
                (new $sClassNamespace())->work($loop, $aMessage['message']);
                $channel->ack($message);
            },
            BUNNY_WORKER_CHANNEL_NAME
        );
    }
);
$loop->run();