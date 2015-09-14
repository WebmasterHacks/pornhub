<?php

namespace WebmasterHacks\Pornhub;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Video
{
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
     * @var Pornstar[]
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
    public function url()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function title()
    {
        $this->parseIfNeeded();

        return $this->title;
    }

    /**
     * @return Pornstar[]
     */
    public function pornstars()
    {
        $this->parseIfNeeded();

        return $this->pornstars;
    }

    /**
     * @return array
     */
    public function categories()
    {
        $this->parseIfNeeded();

        return $this->categories;
    }

    /**
     * @return array
     */
    public function tags()
    {
        $this->parseIfNeeded();

        return $this->tags;
    }

    /**
     * @return string
     */
    public function mp4()
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
            'title' => $this->title(),
            'pornstars' => $this->pornstars(),
            'categories' => $this->categories(),
            'tags' => $this->tags(),
            'mp4' => $this->mp4()
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

        $this->crawler = $this->client->request('GET', $this->url());

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
            return new Pornstar($this->client, $node->link()->getUri());
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
