<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 09.09.2017
 * Time: 13:21
 */

namespace LocationFactory;
include_once __DIR__ .'/../interfaces/JsonFormatter.php';

class Location implements JsonFormatter
{
    public static $ClassNaming = 'Location';
    private $sName;
    private $iChatId;
    private $bRareNotification;
    private $bRaidNotification;
    private $bGymAnalyzing;
    private $bMonsterAnalyzing;
    private $sExcludePokemon;
    private $fNorthLat;
    private $fWestLng;
    private $fSouthLat;
    private $fEastLng;
    private $iMinIv;
    
    public function __construct(
        string $sName,
        int $iChatId,
        bool $bRareNotification,
        bool $bRaidNotification,
        bool $bGymAnalyzing,
        bool $bMonsterAnalyzing,
        array $aNotifyPokemon,
        float $fNorthLat,
        float $fWestLng,
        float $fSouthLat,
        float $fEastLng,
	int $iMinIv
    ) {
        $this->sName = $sName;
        $this->iChatId = $iChatId;
        $this->bRareNotification = $bRareNotification;
        $this->bRaidNotification = $bRaidNotification;
        $this->bGymAnalyzing = $bGymAnalyzing;
        $this->bMonsterAnalyzing = $bMonsterAnalyzing;
        $this->aNotifyPokemon = $aNotifyPokemon;
        $this->fNorthLat = $fNorthLat;
        $this->fWestLng = $fWestLng;
        $this->fSouthLat = $fSouthLat;
        $this->fEastLng = $fEastLng;
	$this->iMinIv = $iMinIv;
    }

    public function getJSON() : string
    {
        $aObjectFrame = [
            'className' => $this::$ClassNaming,
            'message' => [
                'name' => $this->sName,
                'chat_id' => $this->iChatId,
                'isRareNotification' => $this->bRareNotification,
                'isRaidNotification' => $this->bRaidNotification,
                'isGymAnalyzing' => $this->bGymAnalyzing,
                'isSpawnAnalyzing' => $this->bMonsterAnalyzing,
                'notifyPokemon' => $this->aNotifyPokemon,
                'northLat' => $this->fNorthLat,
                'westLng' => $this->fWestLng,
                'southLat' => $this->fSouthLat,
                'eastLng' => $this->fEastLng,
		'min_iv' => $this->iMinIv
            ]
        ];
        return json_encode($aObjectFrame);
    }
}
