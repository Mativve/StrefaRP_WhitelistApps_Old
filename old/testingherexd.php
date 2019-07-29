<?php

include __DIR__.'/vendor/autoload.php';

use RestCord\DiscordClient;

$commander = new DiscordClient(['token' => 'NDUyOTcwOTEyNzc4NTUxMzA2.DfYZzQ.SHa7S3zMUoOCcYgnS41JUxtPOdI']);

$dm = $commander->user->createDm(array("recipient_id" => 426041184788545546));

print_r($dm);

$cid = $dm->id;



?>