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
     * @var PornstarCollection
     */
    protected $pornstars;

    /**
     * @var CategoryCollection
     */
    protected $categories;

    /**
     * @var TagCollection
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
     * @return PornstarCollection
     */
    public function pornstars()
    {
        $this->parseIfNeeded();

        return $this->pornstars;
    }

    /**
     * @return CategoryCollection
     */
    public function categories()
    {
        $this->parseIfNeeded();

        return $this->categories;
    }

    /**
     * @return TagCollection
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

    /**
     * Run the parsers to extract data we need
     * @throws \Exception
     */
    protected function parseIfNeeded()
    {
        try {
            if ($this->parsed) {
                return;
            }

            $this->crawler = $this->client->request('GET', $this->url());

            $this->parseTitle();
            $this->parsePornstars();
            $this->parseCategories();
            $this->parseTags();
            $this->parseMp4();

            $this->parsed = true;
        } catch (\Exception $exception) {
            if ($exception instanceof \InvalidArgumentException) {
                throw new \Exception($exception->getMessage() . " This usually indicates an incorrect video URL");
            }
            throw $exception;
        }
    }

    /**
     *  Parse the Title
     */
    protected function parseTitle()
    {
        $this->title = trim($this->crawler->filter('.video-wrapper .title-container .title')->first()->text());
    }

    /**
     *  Parse the actors/actresses found in the html body
     */
    protected function parsePornstars()
    {
        $pornstarCollection = new PornstarCollection;

        $this->crawler->filter('.video-info-row:contains("Pornstars:") a:not(:contains("Suggest"))')->each(function (
            Crawler $node
        ) use ($pornstarCollection) {
            $pornstarCollection->add(new Pornstar($this->client, trim(strip_tags($node->html())), $node->link()->getUri()));
        });

        $this->pornstars = $pornstarCollection;
    }

    /**
     *  Parse the categories found in the html body
     */
    protected function parseCategories()
    {
        $categoryCollection = new CategoryCollection;

        $this->crawler->filter('.video-info-row:contains("Categories:") a:not(:contains("Suggest"))')->each(function (
            Crawler $node
        ) use ($categoryCollection) {
            $categoryCollection->add(new Category($this->client, trim(strip_tags($node->html())), $node->link()->getUri()));
        });

        $this->categories = $categoryCollection;
    }

    /**
     *  Parse the tags found in the html body
     */
    protected function parseTags()
    {
        $tagCollection = new TagCollection;

        $this->tags = $this->crawler->filter('.video-info-row:contains("Tags:") a:not(:contains("Suggest"))')->each(function (
            Crawler $node
        ) use ($tagCollection) {
            $tagCollection->add(new Tag($this->client, trim(strip_tags($node->html())), $node->link()->getUri()));
        });

        $this->tags = $tagCollection;
    }

    /**
     *  Attempt to find any mp4 links in the body
     */
    protected function parseMp4()
    {
        preg_match('/var player_quality_\d+p = \'(?<mp4>.*?)\'/', $this->crawler->html(), $matches);
        if (key_exists('mp4', $matches)) {
            echo "How'd I get here?";
            $this->mp4 = $matches['mp4'];
        }
    }
}
