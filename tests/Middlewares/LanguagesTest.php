<?php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Middlewares;

class LanguagesTest extends PHPUnit_Framework_TestCase
{
    private function execute($header, $availables, $assert)
    {
        $stack = new Middlewares\Middleware();
        $stack->push(new Middlewares\Languages(['languages' => $availables]));

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

    private function executePath($path, $availables, $assert)
    {
        $stack = new Middlewares\Middleware();
        $stack->push(new Middlewares\BaseUrl(''));
        $stack->push(new Middlewares\Languages([
            'languages' => $availables,
            'fromPath' => true
        ]));

        $request = new Request($path);
        $response = $stack->run($request);

        $this->assertSame($assert, $request->attributes->get('LANGUAGE'));
        $this->assertSame($assert, $response->headers->get('Content-Language'));
    }

    public function testLanguagePath()
    {
        $this->executePath('/es', null, null);
        $this->executePath('/es', ['es', 'gl'], 'es');
        $this->executePath('/en', ['es', 'gl'], 'es');
        $this->executePath('/en/es/gl', ['en', 'gl'], 'en');
        $this->executePath('', ['en', 'gl'], 'en');
    }
}
