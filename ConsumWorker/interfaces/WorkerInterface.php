<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 11.09.2017
 * Time: 18:32
 */

namespace ConsumWorker;

use React\EventLoop\StreamSelectLoop;

interface WorkerInterface
{
    public function work(StreamSelectLoop $loop, array $aMessage);
}