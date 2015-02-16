<?php
use Fol\Http\ResponseCookies;

class ResponseCookiesTest extends PHPUnit_Framework_TestCase
{
    public function testCookies()
    {
        $cookies = new ResponseCookies();

        $cookies->setFromString('Set-Cookie: UserID=JohnDoe; Max-Age=3600; Version=1');

        $this->assertSame(1, $cookies->length());
        $this->assertTrue($cookies->has('UserID'));
        $this->assertEquals('JohnDoe', $cookies->get('UserID')['value']);

        $cookies->set('Other', 'value');

        $this->assertEquals(2, $cookies->length());
        $this->assertTrue($cookies->has('Other'));
        $this->assertEquals('value', $cookies->get('Other')['value']);

        $this->assertNull($cookies->get('Other')['path']);
        $this->assertNull($cookies->get('Other')['domain']);

        $cookies->setDelete('UserID');
        $this->assertEquals(1, $cookies->get('UserID')['expires']);

        $cookies->delete('UserID');
        $this->assertFalse($cookies->has('UserID'));
        $this->assertSame(1, $cookies->length());

        $this->assertSame('Other=value; deleted; expires=Thu, 01-Jan-1970 00:00:00 GMT;', $cookies->getAsString('Other'));
    }
}
