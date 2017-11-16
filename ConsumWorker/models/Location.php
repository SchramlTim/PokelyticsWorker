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

class Location implements WorkerInterface
{

    public function work(StreamSelectLoop $loop, array $aMessage)
    {
        $aParams = [
            'n' => $aMessage['northLat'],
            'w' => $aMessage['westLng'],
            's' => $aMessage['southLat'],
            'e' => $aMessage['eastLng'],
            'gid' => 0
        ];
        
        $oGoMapConnector = new GoMapConnector($loop);
        $oGoMapConnector->getContent($aParams)->then(
            function ($result) use (&$loop, $aMessage) {
                if ($aMessage['isRareNotification']) {
                    $aQueueMessage[] = [
                        'className' => 'NotifyTopSpawns',
                        'message' => [
                            'location' => $aMessage,
                            'json' => $result['pokemons']
                        ]
                    ];
                }
                if ($aMessage['isRaidNotification'] & false) {
                    $aQueueMessage[] = [
                        'className' => 'NotifyRaids',
                        'message' => [
                            'location' => $aMessage,
                            'json' => $result['gyms']
                        ]
                    ];
                }
                if ($aMessage['isGymAnalyzing']) {
                    $aQueueMessage[] = [
                        'className' => 'AnalyzeGyms',
                        'message' => [
                            'location' => $aMessage,
                            'json' => $result['gyms']
                        ]
                    ];
                }
                if ($aMessage['isSpawnAnalyzing']) {
                    $aQueueMessage[] = [
                        'className' => 'AnalyzeSpawns',
                        'message' => [
                            'location' => $aMessage,
                            'json' => $result['pokemons']
                        ]
                    ];
                }
		
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
                        foreach ($aQueueMessage as $aMessage) {
                            $oBunnyChannel->publish(
                                json_encode($aMessage),
                                [],
                                '',
                                BUNNY_WORKER_CHANNEL_NAME
                            );
                        }
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
    }
}
