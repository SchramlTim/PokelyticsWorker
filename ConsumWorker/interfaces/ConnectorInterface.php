<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 11.09.2017
 * Time: 18:48
 */

namespace ConsumWorker;

interface ConnectorInterface
{
    public function getContent(array $aParams);
}