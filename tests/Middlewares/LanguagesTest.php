<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;
use Fol\Http\Middlewares;

class LanguagesTest extends PHPUnit_Framework_TestCase
{
    private function execute($header, $available, $assert)
    {
        $stack = new MiddlewareStack();
        $stack->push(new Middlewares\Languages($available));

        $request = new Request('/', 'get', ['Accept-Language' => $header]);
        $response = $stack->run($request);

        $this->assertSame($assert, $request->attributes->get('LANGUAGE'));
        $this->assertSame($assert, $response->headers->get('Content-Language'));
    }

    public function testLanguages()
    {
        $this->execute('gl-es, es;q=0.8, en;q=0.7', null, 'gl');
        $this->execute('gl-es, es;q=0.8, en;q=0.7', ['es', 'en'], 'es');
        $this->execute('gl-es, es;q=0.8, en;q=0.7', ['en', 'es'], 'es');
        $this->execute(null, null, null);
        $this->execute(null, ['es', 'en'], 'es');
        $this->execute(null, ['en', 'es'], 'en');
    }
}
