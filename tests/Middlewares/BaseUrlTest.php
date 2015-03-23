<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Middlewares;

class BaseUrlTest extends PHPUnit_Framework_TestCase
{
    public function testBaseUrl()
    {
        $stack = new Middlewares\Middleware();

        $stack->push(new Middlewares\BaseUrl('http://domain.com/my-new-site'));

        $stack->push(function ($request, $response, $stack) {
            $response->cookies->set('My-cookie', 'value');
            $stack->next();
        });

        $response = $stack->run(new Request('/'));

        $this->assertEquals('/my-new-site', $response->cookies->get('My-cookie')['path']);
        $this->assertEquals('domain.com', $response->cookies->get('My-cookie')['domain']);
    }
}
