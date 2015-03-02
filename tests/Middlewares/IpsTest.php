<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\Middlewares;

class IpsTest extends PHPUnit_Framework_TestCase
{
    public function testBaseUrl()
    {
        $stack = new MiddlewareStack();

        $stack->push(new Middlewares\Ips());

        $request = new Request('/', 'get', [
            'Client-Ip' => 'unknow,123.456.789.10',
            'X-Forwarded' => '123.234.123.10',
        ]);
        $response = $stack->run($request);

        $this->assertEquals('123.234.123.10', $request->attributes->get('IP'));
    }
}
