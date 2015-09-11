<?php

require 'vendor/autoload.php';

use Goutte\Client;

$client = new Client;
$crawler = $client->request('GET', 'https://pornhub.com/view_video.php?viewkey=973043790');
$title = $crawler->filter('.video-wrapper .title-container .title')->first()->text();
echo $title . PHP_EOL;
