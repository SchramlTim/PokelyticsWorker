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

class AnalyzeSpawns implements WorkerInterface
{

    public function work(StreamSelectLoop $loop, array $aMessage)
    {        
        $aLocation = $aMessage['location'];
        $aPokemonResponse = $aMessage['json'];

        $connection = new \KHR\React\Mysql\Client($loop, new \KHR\React\Mysql\Pool(function(){
            return mysqli_connect(DB_ADDRESS, DB_USER_NAME, DB_PASSWORD, DB_TABLE_NAME);
        }, 10));

        $aExcludePokemon = array(13,161,19,16,198,167,98,165,177,187,163,21,46,133,48,220,194,41,183,10,29,32);
        $aSavePokemon = [];
        foreach($aPokemonResponse as $aPokemon) {
            if(!in_array($aPokemon['pokemon_id'],$aExcludePokemon)){
                $aSavePokemon[] = $aPokemon;
	        }
        }

        $counter = 0;
        $addQuery = 'INSERT IGNORE INTO SpawnedPokemon (chat_id, pokemon_id, pokedex_id, spawntime, disappear_time, lat, lng) VALUES ';
        foreach($aSavePokemon as $aPokemon) {
            $entity = $aPokemon;
            if(!in_array($entity['pokemon_id'],$aExcludePokemon)){
                $addQuery .= '(' . $aLocation['chat_id'] . ', "' . $entity['eid'] . '",' . $entity['pokemon_id'] . ', UNIX_TIMESTAMP(),' . $entity['disappear_time'] . ',' . $entity['latitude'] . ',' . $entity['longitude'] . ')';
                if ($counter < count($aSavePokemon) - 1) {
                    $addQuery .= ',';
                }
            }
            $counter++;
        }

        $connection->query($addQuery)->then(
            function ($result) use (&$loop) {

            },
            function ($error) {
                //echo "error";
            }
        );

        
    }
}
