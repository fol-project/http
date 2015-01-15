<?php
use Fol\Http\Request;
use Fol\Http\Handler;
use Fol\Http\Router\Router;

require_once dirname(__DIR__).'/src/autoload.php';

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $router = new Router();

        $router->map('index', [
            'path' => '/',
            'target' => function ($request, $response) {
                $response->getBody()->write('This is the index');
            }
        ]);

        $router->map('post', [
            'path' => '/post',
            'method' => 'POST',
            'target' => function ($request, $response) {
                $response->getBody()->write('This is POST');
            }
        ]);

        $router->map('get-post', [
            'path' => '/get/post',
            'method' => ['POST', 'GET'],
            'target' => function ($request, $response) {
                $response->getBody()->write('This is GET/POST');
            }
        ]);

        $router->map('put', [
            'path' => '/put/{id}',
            'filters' => [
                'id' => '[\d]+',
            ],
            'method' => ['PUT'],
            'target' => function ($request, $response) {
                $response->getBody()->write('This is PUT/'.$request->attributes['id']);
            }
        ]);

        $router->map('subrequest', [
            'path' => '/subrequest',
            'target' => function ($request, $response) use ($router) {
                $response->getBody()->write('This is a subrequest: ');

                $r = $router->dispatch(new Request('http://domain.com/post', 'POST'));

                $response->getBody()->write((string) $r->getBody());
            }
        ]);

        $router->setError(function ($request, $response) {
            $error = $request->attributes->get('error');

            return 'Error '.$error->getCode().'/'.$error->getMessage();
        });

        $handler = new Handler(new Request('http://domain.com'));

        $response = $router->run($handler);
        $this->assertEquals('This is the index', (string) $response->getBody());

        $response = $router->dispatch(new Request('/'));
        $this->assertEquals('This is the index', (string) $response->getBody());

        $response = $router->dispatch(new Request('http://domain.com/post', 'POST'));
        $this->assertEquals('This is POST', (string) $response->getBody());

        $response = $router->dispatch(new Request('http://domain.com/put/23', 'PUT'));
        $this->assertEquals('This is PUT/23', (string) $response->getBody());

        $response = $router->dispatch(new Request('http://domain.com/put/2.3', 'PUT'));
        $this->assertEquals('Error 404/Not found', (string) $response->getBody());

        $response = $router->dispatch(new Request('http://domain.com/subrequest'));
        $this->assertEquals('This is a subrequest: This is POST', (string) $response->getBody());

        $response = $router->dispatch(new Request('http://domain.com/post', 'GET'));
        $this->assertEquals('Error 404/Not found', (string) $response->getBody());

        $this->assertEquals('http://domain.com/', $router->getUrl('index'));
        $this->assertEquals('http://domain.com/post', $router->getUrl('post'));
        $this->assertEquals('http://domain.com/get/post', $router->getUrl('get-post'));
        $this->assertEquals('http://domain.com/put/34', $router->getUrl('put', ['id' => '34']));
        $this->assertEquals('http://domain.com/put/34?name=oscar', $router->getUrl('put', ['id' => '34', 'name' => 'oscar']));
    }
}
