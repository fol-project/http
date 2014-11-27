<?php
use Fol\Http\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $request = new Request();
    }
}
