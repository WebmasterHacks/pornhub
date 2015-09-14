<?php

require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;
use WebmasterHacks\Pornhub\Video;
use WebmasterHacks\Pornhub\Pornstar;

$client = new Client;
$client->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/44.0.2403.89 Chrome/44.0.2403.89 Safari/537.36');

$video = new Video($client, 'http://www.pornhub.com/view_video.php?viewkey=158407481');

foreach ($video->pornstars() as $pornstar) {
    echo $pornstar->url() . PHP_EOL;
}
