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

class NotifyTopSpawns implements WorkerInterface
{

    public function work(StreamSelectLoop $loop, array $aMessage)
    {        
        $aLocation = $aMessage['location'];
        $aPokemonResponse = $aMessage['json'];
        $aNotifyPokemon = array_keys($aLocation['notifyPokemon']);

        $connection = new \KHR\React\Mysql\Client($loop, new \KHR\React\Mysql\Pool(function(){
            return mysqli_connect(DB_ADDRESS, DB_USER_NAME, DB_PASSWORD, DB_TABLE_NAME);
        }, 10));

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
                $connection->query('INSERT INTO NotifiedPokemon (pokemon_id,pokedex_id,chat_id,disappear_time,spawntime,lat,lng) VALUES ('.$aPokemon['eid'].','.$aPokemon['pokemon_id'].','.$aLocation['chat_id'].','.$aPokemon['disappear_time'].','.time().','.$aPokemon['latitude'].','.$aPokemon['longitude'].')')->then(
                    function ($result) use (&$loop,$aPokemon,$aLocation) {
                        $aParams = [
                            'key' => KEY_GOOGLE_MAPS,
                            'latlng' => $aPokemon['latitude'] . ',' . $aPokemon['longitude']
                        ];
                        $oGoogleMapsConnector = new GoogleMapsConnector($loop);
                        $oGoogleMapsConnector->getContent($aParams)->then(
                            function ($result) use (&$loop, $aLocation, $aPokemon) {
                                $aSendMessage = [
                                    'pokemon_info' => $aPokemon,
                                    'address' => $result['formatted_address']
                                ];
                                $aQueueMessage = [
                                    'className' => 'SendMessageToTelegram',
                                    'message' => [
                                        'location' => $aLocation,
                                        'telegram_message' => $aSendMessage
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
                                    function ($oBunnyChannel) use ($aQueueMessage) {
                                        $oBunnyChannel->publish(
                                            json_encode($aQueueMessage),
                                            [],
                                            '',
                                            BUNNY_WORKER_CHANNEL_NAME
                                        );
                                        return $oBunnyChannel;
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
                        );
                    },
                    function ($error) {
                        //echo $error->getMessage().PHP_EOL;
                    }
                );
            }
        }
        
    }
}
