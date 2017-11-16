<?php
require __DIR__ . '/../vendor/autoload.php';

use Bunny\Async\Client;
use Bunny\Channel;

include_once __DIR__ .'/../config.php';
include_once __DIR__ .'/models/Location.php';

/*
 * Async loop*
 */

$loop = React\EventLoop\Factory::create();


/*
 * Periodic Timer, which call every 60 seconds the code
 * inside the timer block.
 */
$loop->addPeriodicTimer(60, function () use (&$loop){

    $GLOBALS['lStartTime'] = microtime(true);
    /*
     * create async mysql connection
     */
    $connection = new \KHR\React\Mysql\Client($loop, new \KHR\React\Mysql\Pool(function(){
        $con =  mysqli_connect(DB_ADDRESS, DB_USER_NAME, DB_PASSWORD, DB_TABLE_NAME);
        return $con;
    }, 1));

    /*
     * get all user where active > 0
     */
    $connection->query('select * from User where active > 0 ')->then(
        function ($result) use (&$loop) {
            $aLocations = $result->all();

            $connection = new \KHR\React\Mysql\Client($loop, new \KHR\React\Mysql\Pool(function(){
                $con =  mysqli_connect(DB_ADDRESS, DB_USER_NAME, DB_PASSWORD, DB_TABLE_NAME);
                if (!$con)
                {
                    echo "Connection error: " . mysqli_connect_error();
                }
                return $con;
            }, 1));
            foreach($aLocations as $aLocation){

                $connection->query('SELECT p.pokedex_id, np.min_iv FROM Pokemon p, NotifyPokemon np WHERE p.pokedex_id = np.pokedex_id and np.chat_id = '.$aLocation['chat_id'])->then(
                    function ($result) use (&$loop) {
                        $aMySqlExcludedPokemon = $result->all();
                        $aNotifyPokemon = [];
                        foreach($aMySqlExcludedPokemon as $mySqlEntity){
                            $aNotifyPokemon[$mySqlEntity['pokedex_id']] = $mySqlEntity['min_iv'];
                        }
                        return $aNotifyPokemon;
                    }
                )->then(
                    function ($result) use (&$loop, $aLocation) {
                        
                        $oLocation = new \LocationFactory\Location(
                            $aLocation['user_name'],
                            $aLocation['chat_id'],
                            $aLocation['bot_pokemon_active'],
                            $aLocation['bot_raid_active'],
                            $aLocation['website_gyms_active'],
                            $aLocation['website_spawns_active'],
                            $result,
                            $aLocation['northLat'],
                            $aLocation['westLng'],
                            $aLocation['southLat'],
                            $aLocation['eastLng'],
			    $aLocation['min_iv']
                        );
                        (new \Bunny\Async\Client($loop,[
                            "host" => "localhost",
                            "port" => 5672,
                            "vhost" => "/",
                            "user" => "guest",
                            "password" => "guest"
                        ]))->connect()->then(
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
                            function ($oBunnyChannel) use ($oLocation){
                                $oBunnyChannel->publish(
                                    $oLocation->getJSON(),
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
            }
        },
        function ($error)  { echo $error->getMessage(); }
    );
});
$loop->run();

