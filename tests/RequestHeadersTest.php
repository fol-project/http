<?php
use Fol\Http\RequestHeaders;

require_once dirname(__DIR__).'/vendor/autoload.php';

class RequestHeadersTest extends PHPUnit_Framework_TestCase
{
    public function testCookies()
    {
        $headers = new RequestHeaders();
        $headers->setFromString('Cookie: $Version=1; Skin=new;');

        $this->assertEquals(2, $headers->cookies->length());
        $this->assertEquals('new', $headers->cookies->get('Skin'));

        $headers->cookies->set('Other', 'value');
        $this->assertEquals(3, $headers->cookies->length());
        $this->assertEquals('value', $headers->cookies->get('Other'));
    }

    public function testDatetime()
    {
        $headers = new RequestHeaders();
        $headers->setDateTime('Date', new \Datetime('31-06-2010 20:35:12'));

        $this->assertEquals('Date: Thu, 01 Jul 2010 18:35:12 GMT', $headers->getAsString('Date'));

        $headers->setFromString('Date: Tue, 15 Nov 1994 08:12:31 GMT');
        $datetime = $headers->getDateTime('date');

        $this->assertEquals('1994-11-15 08:12:31', $datetime->format('Y-m-d H:i:s'));
    }
}
