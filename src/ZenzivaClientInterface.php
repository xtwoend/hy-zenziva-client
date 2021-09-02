<?php

namespace Xtwoend\ZenzivaClient;


interface ZenzivaClientInterface
{
    public function send($to, $text);
    public function sendWaFile($to, $text, $file);
}