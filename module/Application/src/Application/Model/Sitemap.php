<?php
namespace Application\Model;

class Sitemap
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function __construct()
    {
        $this->xml = new \SimpleXMLElement("<urlset></urlset>");
        $this->xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    public function addPage($options)
    {
        $urlXML = $this->xml->addChild('url');

        if($options['lastmod']) {
            $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $options['lastmod']);

            if(!$dt) {
                $dt = \DateTime::createFromFormat('Y-m-d', $options['lastmod']);
            }

            if(!$dt) {
                throw new \Exception('Wrong "lastmod" date format');
            }

            $urlXML->addChild('lastmod', $dt->format(\DateTime::W3C));
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($options['loc'], '/');

        $urlXML->addChild('loc', $url);
        $urlXML->addChild('changefreq', $options['changefreq']);
        $urlXML->addChild('priority', $options['priority']);
    }

    /**
     * @return mixed
     */
    public function getSitemap()
    {
        return $this->xml->asXML();
    }
}