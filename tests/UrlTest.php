<?php
use Fol\Http\Url;

require_once dirname(__DIR__).'/src/autoloader.php';

class UrlTest extends PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $url = new Url('http://blog.com:80/categories/index.php?sort=latest#main');

        $this->assertEquals($url->getScheme(), 'http');
        $this->assertEquals($url->getHost(), 'blog.com');
        $this->assertEquals($url->getPort(), 80);
        $this->assertEquals($url->getPath(), '/categories');
        $this->assertEquals($url->getFilename(), 'index');
        $this->assertEquals($url->getExtension(), 'php');
        $this->assertEquals($url->getFragment(), 'main');

        $url->setScheme('https');
        $url->setHost('news.org');
        $url->setPort(433);
        $url->setPath('trending');
        $url->setFilename('week');
        $url->setExtension('asp');
        $url->setFragment('#menu');

        $this->assertEquals($url->getScheme(), 'https');
        $this->assertEquals($url->getHost(), 'news.org');
        $this->assertEquals($url->getPort(), 433);
        $this->assertEquals($url->getPath(), '/trending');
        $this->assertEquals($url->getFilename(), 'week');
        $this->assertEquals($url->getExtension(), 'asp');
        $this->assertEquals($url->getFragment(), 'menu');

        $this->assertEquals($url->getUrl(true, true), 'https://news.org/trending/week.asp?sort=latest#menu');
        $this->assertEquals($url->getUrl(true), 'https://news.org/trending/week.asp?sort=latest');
        $this->assertEquals($url->getUrl(), 'https://news.org/trending/week.asp');
    }

    public function testPort()
    {
        $url = new Url('http://blog.com');

        $this->assertEquals($url->getPort(), 80);

        $url->setScheme('https');

        $this->assertEquals($url->getPort(), 433);
        $this->assertEquals($url->getUrl(), 'https://blog.com/');
        
        $url->setPort(8888);
        $this->assertEquals($url->getUrl(), 'https://blog.com:8888/');
    }

    public function testQuery()
    {
        $url = new Url('http://blog.com?sort=latest&page=1');

        $this->assertEquals(2, $url->query->length());
        $this->assertEquals('latest', $url->query->get('sort'));
        $this->assertEquals('1', $url->query['page']);
        $this->assertEquals('default', $url->query->get('no-defined', 'default'));

        $url->query->set('from', 'now');
        $this->assertEquals($url->getUrl(true), 'http://blog.com/?sort=latest&page=1&from=now');
    }
}
