<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 11.09.2017
 * Time: 19:55
 */

namespace ConsumWorker;
use React\Promise\Promise;

include_once __DIR__ . '/WebConnector.php';

class GoogleMapsConnector extends WebConnector
{
    function getContent(array $aParams) : Promise
    {
        $oCurl = new \KHR\React\Curl\Curl($this->loop);
        $oCurl->client->setMaxRequest(3);
        $oCurl->client->setSleep(6, 1.0, false); // 6 request in 1 second
        $oCurl->client->setCurlOption([
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
        
        return $oCurl->get(CONNECTOR_GOOGLE_MAPS_URL.$this->transformGetParams($aParams))->then(
            function($result) {
		$response = $result->getJson(true);
		if(isset($response['error_message'])){
			echo $response['error_message'].PHP_EOL;	
			return '';
		}	
                return $result->getJson(true)['results'][0];
            }
        );
    }
}
