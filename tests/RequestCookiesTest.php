<?php
use Fol\Http\RequestCookies;

class RequestCookiesTest extends PHPUnit_Framework_TestCase
{
    public function testCookies()
    {
        $cookies = new RequestCookies();
        $cookies->setFromString('Cookie: $Version=1; Skin=new;');

        $this->assertEquals(2, $cookies->length());
        $this->assertEquals('new', $cookies->get('Skin'));

        $cookies->set('Other', 'value');
        $this->assertEquals(3, $cookies->length());
        $this->assertEquals('value', $cookies->get('Other'));
    }
}
