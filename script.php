<?php

require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Video {

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $pornstars;

    /**
     * @var array
     */
    protected $categories;

    /**
     * @var array
     */
    protected $tags;

    /**
     * @var string
     */
    protected $mp4;

    /**
     * @var bool
     */
    protected $parsed = false;

    /**
     * @param Client $client
     * @param string $url
     */
    public function __construct(Client $client, $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $this->parseIfNeeded();

        return $this->title;
    }

    /**
     * @return array
     */
    public function getPornstars()
    {
        $this->parseIfNeeded();

        return $this->pornstars;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $this->parseIfNeeded();

        return $this->categories;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $this->parseIfNeeded();

        return $this->tags;
    }

    /**
     * @return string
     */
    public function getMp4()
    {
        $this->parseIfNeeded();

        return $this->mp4;
    }

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    protected function parseIfNeeded()
    {
        if ($this->parsed) return;

        $this->crawler = $this->client->request('GET', $this->getUrl());

        $this->parseTitle();
        $this->parsePornstars();
        $this->parseCategories();
        $this->parseTags();
        $this->parseMp4();

        $this->parsed = true;
    }

    protected function parseTitle()
    {
        $this->title = trim($this->crawler->filter('.video-wrapper .title-container .title')->first()->text());
    }

    protected function parsePornstars()
    {
        $this->pornstars = $this->crawler->filter('.video-info-row:contains("Pornstars:") a:not(:contains("Suggest"))')->each(function(Crawler $node) {
            return trim($node->text());
        });
    }

    protected function parseCategories()
    {
        $this->categories = $this->crawler->filter('.video-info-row:contains("Categories:") a:not(:contains("Suggest"))')->each(function(Crawler $node) {
            return trim($node->text());
        });
    }

    protected function parseTags()
    {
        $this->tags = $this->crawler->filter('.video-info-row:contains("Tags:") a:not(:contains("Suggest"))')->each(function(Crawler $node) {
            return trim($node->text());
        });
    }

    protected function parseMp4()
    {
        preg_match('/var player_quality_\d+p = \'(?<mp4>.*?)\'/', $this->crawler->html(), $matches);
        $this->mp4 = $matches['mp4'];
    }

}

class Pornstar {

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @param Client $client
     * @param string $url
     */
    public function __construct(Client $client, $url)
    {
        $this->client = $client;
        $this->url = $url;
        $this->crawler = $this->client->request('GET', $url);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getVideoUrls()
    {
        return $this->crawler->filter('.videos .videoblock .title a')->each(function(Crawler $node) {
            return $node->link()->getUri();
        });
    }

    /**
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->crawler->filter('.pagination3 .page_next')->count() > 0 ? true : false;
    }

    public function goToNextPage()
    {
        $link = $this->crawler->filter('.pagination3 .page_next a')->link();
        $this->crawler = $this->client->click($link);
    }

    /**
     * @return array
     */
    public function getAllVideoUrls()
    {
        $urls = $this->getVideoUrls();

        while ($this->hasNextPage()) {
            $this->goToNextPage();
            $urls = array_merge($urls, $this->getVideoUrls());
        }

        return $urls;
    }

    /**
     * @return Video[]
     */
    public function getVideos()
    {
        return array_map(function($url) {
            return new Video($this->client, $url);
        }, $this->getVideoUrls());
    }

    /**
     * @return Video[]
     */
    public function getAllVideos()
    {
        return array_map(function($url) {
            return new Video($this->client, $url);
        }, $this->getAllVideoUrls());
    }

}

$client = new Client;
$client->setHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/44.0.2403.89 Chrome/44.0.2403.89 Safari/537.36');
$pornstar = new Pornstar($client, 'http://www.pornhub.com/pornstar/madison-ivy');

foreach ($pornstar->getAllVideos() as $video) {
    echo $video->getTitle() . PHP_EOL;
}
