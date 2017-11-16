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

class AnalyzeGyms implements WorkerInterface
{

    public function work(StreamSelectLoop $loop, array $aMessage)
    {        
        $aLocation = $aMessage['location'];
        $aGymResponse = $aMessage['json'];

        $connection = new \KHR\React\Mysql\Client($loop, new \KHR\React\Mysql\Pool(function(){
            return mysqli_connect(DB_ADDRESS, DB_USER_NAME, DB_PASSWORD, DB_TABLE_NAME);
        }, 10));

        $addQueryGym = 'INSERT IGNORE INTO Gyms (chat_id, gym_id, name, team_id, latitude, longitude, gym_points, is_in_battle, time_occupied, ts, raid_start, raid_begin, raid_end, raid_level, raid_pokemon_id, raid_pokemon_cp) VALUES ';
        $counter = 0;
        foreach($aGymResponse as $aGym) {
            $entity = $aGym;
            $iCurUnixTime = time();
            $iRaidStart = 0;
            $iRaidBegin = 0;
            $iRaidEnd = 0;
            $iRaidLvl = 0;
            $iRaidPokemonId = 0;
            $iRaidPokemonCp = 0;
            
            if ((isset($entity['rs']) && $entity['rs'] <= $iCurUnixTime) & (isset($entity['re']) && $entity['re'] >= $iCurUnixTime)) {
                $iRaidStart = $entity['rs'];
                $iRaidBegin = $entity['rb'];
                $iRaidEnd = $entity['re'];
                $iRaidLvl = $entity['lvl'];
                if(isset($entity['rpid']) && isset($entity['rcp'])){
                    $iRaidPokemonId = $entity['rpid'];
                    $iRaidPokemonCp = $entity['rcp'];
                }
            }

            $addQueryGym .= '(' . $aLocation['chat_id'] . ', ' . $entity['gym_id'] . ', "' . str_replace('"', '', $entity['name']) . '", ' . $entity['team_id'] . ', ' . $entity['latitude'] . ', ' . $entity['longitude'] . ', ' . $entity['gym_points'] . ', ' . $entity['is_in_battle'] . ', ' . $entity['time_ocuppied'] . ', ' . $entity['ts'] . ', ' . $iRaidStart . ', ' . $iRaidBegin . ', ' . $iRaidEnd . ', ' . $iRaidLvl . ', ' . $iRaidPokemonId . ', ' . $iRaidPokemonCp . ')';

            if ($counter < count($aGymResponse) - 1) {
                $addQueryGym .= ',';
            }
            $counter++;
        }
        $addQueryGym .= ' ON DUPLICATE KEY UPDATE team_id=values(team_id), gym_points=values(gym_points), is_in_battle=values(is_in_battle), time_occupied=values(time_occupied), ts=values(ts), raid_start=values(raid_start), raid_begin=values(raid_begin), raid_end=values(raid_end), raid_level=values(raid_level), raid_pokemon_id=values(raid_pokemon_id), raid_pokemon_cp=values(raid_pokemon_cp)';

        $connection->query($addQueryGym)->then(
            function ($result) use (&$loop) {

            },
            function ($error) {
                //echo "error";
            }
        );

        
    }
}