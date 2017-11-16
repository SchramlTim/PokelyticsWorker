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

class Notification implements WorkerInterface
{

    public function work(StreamSelectLoop $loop, array $aMessage)
    {
        $aLocation = $aMessage['location'];
        $aPokemonResponse = $aMessage['json'];
        $aNotifyPokemon = array_keys($aLocation['notifyPokemon']);
        $oGoogleMapsConnector = new GoogleMapsConnector($loop);
        $aSendMessages = [];
        foreach($aPokemonResponse as $aPokemon) {
            if(
                (
                    in_array($aPokemon['pokemon_id'], $aNotifyPokemon)
                    && (
                        (isset($aPokemon['iv']) && $aPokemon['iv'] >= $aLocation['notifyPokemon'][$aPokemon['pokemon_id']])
                        || !isset($aPokemon['iv'])
                    )
                )
                || (
                    isset($aPokemon['iv'])
                    && $aPokemon['iv'] >= $aLocation['min_iv']
                )
            ){
                $aParams = [
                    'key' => KEY_GOOGLE_MAPS,
                    'latlng' => $aPokemon['latitude'].','.$aPokemon['longitude']
                ];
                $oGoogleMapsConnector->getContent($aParams)->then(
                    function($result) use ($aSendMessages,$aPokemon){
                        $aSendMessages[] = [
                            'pokemon_info' => $aPokemon,
                            'address' => $result['formatted_address']
                        ];
                    }
                );
            }

            $aQueueMessage = [
                'className' => 'SendTelegram',
                'message' => [
                    'location' => $aLocation,
                    'telegram_messages' => $aSendMessages
                ]
            ];

            (new \Bunny\Async\Client($loop))->connect()->then(
                function (Client $client) {
                    return $client->channel();
                },
                function ($e) {
                    echo $e->getMessage();
                }
            )->then(
                function (Channel $oBunnyChannel) {
                    return $oBunnyChannel->queueDeclare(
                        BUNNY_WORKER_CHANNEL_NAME,
                        false,
                        false,
                        false,
                        false
                    )->then(
                        function () use ($oBunnyChannel) {
                            return $oBunnyChannel;
                        }
                    );
                }
            )->then(
                function ($oBunnyChannel) use ($aQueueMessage){
                    $oBunnyChannel->publish(
                        json_encode($aQueueMessage),
                        [],
                        '',
                        BUNNY_WORKER_CHANNEL_NAME
                    );

                },
                function ($e) {
                    echo $e->getMessage();
                }
            )->then(
                function ($oBunnyChannel) {
                    $oBunnyClient = $oBunnyChannel->getClient();
                    $oBunnyChannel->close();
                    $oBunnyClient->disconnect();
                }
            );
        }
    }
}