<?php
use Fol\Http\RequestHeaders;

require_once dirname(__DIR__).'/vendor/autoload.php';

class HeadersTest extends PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $headers = new RequestHeaders();
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
    }
}
