<?php

require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;
use WebmasterHacks\Pornhub\Video;
use WebmasterHacks\Pornhub\Pornstar;
use WebmasterHacks\Pornhub\Category;
use WebmasterHacks\Pornhub\Tag;

$client = new Client;
$client->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/44.0.2403.89 Chrome/44.0.2403.89 Safari/537.36');

$video = new Video($client, 'http://www.pornhub.com/view_video.php?viewkey=ph55f4113f77d67');

echo 'Pornstars:' . PHP_EOL;

$video->pornstars()->each(function(Pornstar $pornstar) {
    echo $pornstar->url() . PHP_EOL;
});

echo 'Categories:' . PHP_EOL;

$video->categories()->each(function(Category $category) {
    echo $category->url() . PHP_EOL;
});

echo 'Tags:' . PHP_EOL;

$video->tags()->each(function(Tag $tag) {
    echo $tag->url() . PHP_EOL;
});
