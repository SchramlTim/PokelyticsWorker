<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 11.09.2017
 * Time: 18:30
 */

namespace ConsumWorker;

use React\EventLoop\StreamSelectLoop;

include_once __DIR__ .'/../interfaces/ConnectorInterface.php';

abstract class WebConnector implements ConnectorInterface
{
    protected $loop = null;

    function __construct(StreamSelectLoop &$loop)
    {
        $this->loop = $loop;
    }
    
    abstract function getContent(array $aParams);

    protected function transformGetParams(array $aParams) : string
    {
        $sGetUrlArtifact = '';
        $iCounter = 1;
        foreach($aParams as $sKey => $sParamValue){
            $sGetUrlArtifact .= $sKey.'='.(is_array($sParamValue) ? '['.implode(',', $sParamValue).']' : $sParamValue);
            if($iCounter == count($aParams)) continue;
            $sGetUrlArtifact .= '&';
            $iCounter++;
        }
        return $sGetUrlArtifact;
    }
}