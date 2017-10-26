<?php

require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;
use WebmasterHacks\Pornhub\Video;
use WebmasterHacks\Pornhub\Pornstar;
use WebmasterHacks\Pornhub\Category;
use WebmasterHacks\Pornhub\Tag;

$client = new Client;
$client->setHeader('User-Agent',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/44.0.2403.89 Chrome/44.0.2403.89 Safari/537.36');

$sfw = new Video($client, 'https://www.pornhub.com/view_video.php?viewkey=ph57474dbe92b9f');
$nsfw = new Video($client, 'https://www.pornhub.com/view_video.php?viewkey=293787302');

$videos = [
  $sfw,
  $nsfw
];
/** @var Video $video */
foreach ($videos as $video) {


    echo $video->title() . PHP_EOL;
    echo $video->url() . PHP_EOL . PHP_EOL;

    echo 'Pornstars:' . PHP_EOL;

    if ($video->pornstars()->count() > 0) {
        $video->pornstars()->each(function (Pornstar $pornstar) {
            echo $pornstar->getTitle() . ": " . $pornstar->url() . PHP_EOL;
        });
    } else {
        echo "No one of note" . PHP_EOL;
    }

    echo 'Categories:' . PHP_EOL;

    $video->categories()->each(function (Category $category) {
        echo $category->getTitle() . ": " . $category->url() . PHP_EOL;
    });

    echo 'Tags:' . PHP_EOL;

    $video->tags()->each(function (Tag $tag) {
        echo $tag->getTitle() . ": " . $tag->url() . PHP_EOL;
    });

}