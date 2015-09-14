<?php

namespace WebmasterHacks\Pornhub;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

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
    public function url()
    {
        return $this->url;
    }

    /**
     * @return Video[]
     */
    public function videos()
    {
        return array_map(function($url) {
            return new Video($this->client, $url);
        }, $this->allVideoUrls());
    }

    /**
     * @return array
     */
    protected function videoUrls()
    {
        return $this->crawler->filter('.videos .videoblock .title a')->each(function(Crawler $node) {
            return $node->link()->getUri();
        });
    }

    /**
     * @return bool
     */
    protected function hasNextPage()
    {
        return $this->crawler->filter('.pagination3 .page_next')->count() > 0 ? true : false;
    }

    protected function goToNextPage()
    {
        $link = $this->crawler->filter('.pagination3 .page_next a')->link();
        $this->crawler = $this->client->click($link);
    }

    /**
     * @return array
     */
    protected function allVideoUrls()
    {
        $urls = $this->videoUrls();

        while ($this->hasNextPage()) {
            $this->goToNextPage();
            $urls = array_merge($urls, $this->videoUrls());
        }

        return $urls;
    }
}
