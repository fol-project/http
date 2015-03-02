<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\Sessions\Session;
use Fol\Http\Middlewares;

class FormatDetectorTest extends PHPUnit_Framework_TestCase
{
    private function execute($url, $header, $availables, $assertFormat, $assertMime)
    {
        $stack = new MiddlewareStack();
        $stack->push(new Middlewares\FormatDetector($availables));

        $request = new Request($url, 'get', ['Accept' => $header]);
        $response = $stack->run($request);

        $this->assertEquals($response->headers->get('Content-Type'), "{$assertMime}; charset=UTF-8");
        $this->assertEquals($request->attributes->get('FORMAT'), $assertFormat);
    }

    public function testOne()
    {
        $this->execute('/test.json', null, null, 'json', 'application/json');
        $this->execute('/test.json', null, ['gif', 'html'], 'html', 'text/html');

        $this->execute('/', 'application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5', null, 'xml', 'text/xml');
        $this->execute('/', null, null, 'html', 'text/html');
        $this->execute('/', 'application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5', ['html', 'json'], 'html', 'text/html');
    }
}
