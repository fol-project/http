<?php
use Fol\Http\Request;
use Fol\Http\RequestResponseHandler;
use Fol\Http\Router\Router;

require_once dirname(__DIR__).'/src/autoload.php';

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $handler = new RequestResponseHandler(new Request('http://domain.com'));
        $router = new Router($handler);

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
            'method' => ['PUT'],
            'target' => function ($request, $response) {
                $response->getBody()->write('This is PUT/'.$request->attributes['id']);
            }
        ]);

        $router->map('subrequest', [
            'path' => '/subrequest',
            'target' => function ($request, $response) use ($router) {
                $response->getBody()->write('This is a subrequest: ');

                $r = $router->handle(new Request('http://domain.com/post', 'POST'));

                $response->getBody()->write((string) $r->getBody());
            }
        ]);

        $router->setError(function ($request, $response) {
            $error = $request->attributes->get('error');

            return 'Error '.$error->getCode().'/'.$error->getMessage();
        });

        $response = $router->handle($handler->getRequest());
        $this->assertEquals('This is the index', (string) $response->getBody());

        $response = $router->handle(new Request('/'));
        $this->assertEquals('This is the index', (string) $response->getBody());

        $response = $router->handle(new Request('http://domain.com/post', 'POST'));
        $this->assertEquals('This is POST', (string) $response->getBody());

        $response = $router->handle(new Request('http://domain.com/put/23', 'PUT'));
        $this->assertEquals('This is PUT/23', (string) $response->getBody());

        $response = $router->handle(new Request('http://domain.com/subrequest'));
        $this->assertEquals('This is a subrequest: This is POST', (string) $response->getBody());

        $response = $router->handle(new Request('http://domain.com/post', 'GET'));
        $this->assertEquals('Error 404/Not found', (string) $response->getBody());
    }
}
