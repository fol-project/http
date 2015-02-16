<?php
use Fol\Http\Request;
use Fol\Http\Globals;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $request = new Request('http://mydomain.com?hello=world', 'post', ['Accept' => 'text/plain']);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('text/plain', $request->headers->get('Accept'));
        $this->assertEquals('txt', $request->getFormat());
        $this->assertEquals('world', $request->query->get('hello'));
        $this->assertNull($request->getLanguage());
    }

    public function testPreferredLanguage()
    {
        $request = new Request('http://mydomain.com?hello=world', 'post', ['Accept-Language' => 'gl-es, es;q=0.8, en;q=0.7']);

        $this->assertEquals('gl', $request->getPreferredLanguage());
        $this->assertEquals('es', $request->getPreferredLanguage(['es', 'en']));
        $this->assertEquals('es', $request->getPreferredLanguage(['en', 'es']));

        $request = new Request();

        $this->assertNull($request->getPreferredLanguage());
        $this->assertEquals('es', $request->getPreferredLanguage(['es', 'en']));
        $this->assertEquals('en', $request->getPreferredLanguage(['en', 'es']));
    }

    public function testIp()
    {
        $request = new Request('', 'get', [
            'Client-Ip' => '0.0.0.0, 1.1.1.1',
            'X-Forwarded' => '2.2.2.2, unknow, 3.3.3.3'
        ]);

        $this->assertEquals($request->getIps(), [
            '0.0.0.0',
            '1.1.1.1',
            '2.2.2.2',
            '3.3.3.3'
        ]);

        $this->assertEquals($request->getIp(), '0.0.0.0');
    }

    public function testAjax()
    {
        $request = new Request('', 'get', ['X-Requested-With' => 'xmlhttprequest']);

        $this->assertTrue($request->isAjax());

        $request->headers->set('X-Requested-With', 'none');
        $this->assertFalse($request->isAjax());

        $request->headers->delete('X-Requested-With');
        $this->assertFalse($request->isAjax());
    }

    public function testBasicAuthentication()
    {
        $request = new Request('', 'get', [
            'Authorization' => 'Basic:'.base64_encode('oscarotero:a-miña-contraseña')
        ]);

        $this->assertEquals('oscarotero', $request->getUser());
        $this->assertEquals('a-miña-contraseña', $request->getPassword());
        $this->assertFalse($request->checkPassword('foo', 'bar'));
    }

    public function testCreateFromGlobal()
    {
        $g = include __DIR__.'/files/global-request.php';
        $global = new Globals($g['_SERVER'], $g['_GET'], $g['_POST'], $g['_FILES'], $g['_COOKIE'], $g['input']);

        $request = Request::createFromGlobals($global);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('http://localhost/test.php', $request->url->getUrl());
        $this->assertEquals(80, $request->url->getPort());
        $this->assertEquals('html', $request->getFormat());
        $this->assertEquals('gl', $request->getPreferredLanguage(['gl']));
        $this->assertEquals('', (string) $request->getBody());
    }
}
