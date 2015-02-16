<?php
use Fol\Http\Headers;

class HeadersTest extends PHPUnit_Framework_TestCase
{
    public function testValues()
    {
        $headers = new Headers();
        $headers->set('Accept', 'text/plain');

        $this->assertEquals('text/plain', $headers->get('Accept'));
        $this->assertEquals('text/plain', $headers->get('ACCEPT'));
        $this->assertEquals('text/plain', $headers->get('accept'));
        $this->assertEquals('text/plain', $headers->get('AcCePt'));

        $this->assertEquals('Accept: text/plain', $headers->getAsString('Accept'));
        $this->assertTrue($headers->has('Accept'));
        $this->assertFalse($headers->has('No-Accept'));

        $headers->setFromString('accept-language: en-US');

        $this->assertEquals('en-US', $headers->get('Accept-Language'));

        $headers->delete('Accept-Language');
        $this->assertNull($headers->get('Accept-Language'));
        $this->assertFalse($headers->has('Accept-Language'));
    }

    public function testDatetime()
    {
        $headers = new Headers();
        $headers->setDateTime('Date', new \Datetime('31-06-2010 18:35:12', new \DateTimeZone('GMT')));

        $this->assertEquals('Date: Thu, 01 Jul 2010 18:35:12 GMT', $headers->getAsString('Date'));

        $headers->setFromString('Date: Tue, 15 Nov 1994 08:12:31 GMT');
        $datetime = $headers->getDateTime('date');

        $this->assertEquals('1994-11-15 08:12:31', $datetime->format('Y-m-d H:i:s'));
    }
}
