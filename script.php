<?php

require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;

class Video {

    protected $client;
    protected $url;
    protected $crawler;

    public function __construct($client, $url)
    {
        $this->client = $client;
        $this->url = $url;
        $this->crawler = $crawler = $client->request('GET', $url);

        $this->parseTitle();
        $this->parsePornstars();
        $this->parseCategories();
        $this->parseTags();
        $this->parseMp4();
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getPornstars()
    {
        return $this->pornstars;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getMp4()
    {
        return $this->mp4;
    }

    public function toArray()
    {
        return [
            'title' => $this->getTitle(),
            'pornstars' => $this->getPornstars(),
            'categories' => $this->getCategories(),
            'tags' => $this->getTags(),
            'mp4' => $this->getMp4()
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    protected function parseTitle()
    {
        $this->title = trim($this->crawler->filter('.video-wrapper .title-container .title')->first()->text());
    }

    protected function parsePornstars()
    {
        $this->pornstars = $this->crawler->filter('.video-info-row:contains("Pornstars:") a:not(:contains("Suggest"))')->each(function($node) {
            return trim($node->text());
        });
    }

    protected function parseCategories()
    {
        $this->categories = $this->crawler->filter('.video-info-row:contains("Categories:") a:not(:contains("Suggest"))')->each(function($node) {
            return trim($node->text());
        });
    }

    protected function parseTags()
    {
        $this->tags = $this->crawler->filter('.video-info-row:contains("Tags:") a:not(:contains("Suggest"))')->each(function($node) {
            return trim($node->text());
        });
    }

    protected function parseMp4()
    {
        preg_match('/var player_quality_720p = \'(?<mp4>.*?)\'/', $this->crawler->html(), $matches);
        $this->mp4 = $matches['mp4'];
    }

}

$client = new Client;

$video = new Video($client, 'https://pornhub.com/view_video.php?viewkey=973043790');
var_dump($video->getTitle());
var_dump($video->getPornstars());
var_dump($video->getCategories());
var_dump($video->getTags());
var_dump($video->getMp4());
var_dump($video->toArray());
var_dump($video->toJson());
