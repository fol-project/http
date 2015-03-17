<?php
use Fol\Http\RequestCookies;

class RequestCookiesTest extends PHPUnit_Framework_TestCase
{
    public function testCookies()
    {
        $cookies = new RequestCookies();
        $cookies->setFromString('Cookie: $Version=1; Skin=new;');

        $this->assertCount(2, $cookies);
        $this->assertEquals('new', $cookies->get('Skin'));

        $cookies->set('Other', 'value');
        $this->assertCount(3, $cookies);
        $this->assertEquals('value', $cookies->get('Other'));
    }
}
