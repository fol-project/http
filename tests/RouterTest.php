<?php
use Fol\Http\MiddlewareStack;
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\HttpException;
use Fol\Http\Router\Router;

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
            'target' => function ($request, $response, $app, $stack) {
                $response->getBody()->write('This is a subrequest: ');

                $newStack = clone $stack;

                $newStack->run(new Request('http://domain.com/post', 'POST'), new Response());

                $response->getBody()->write((string) $newStack->getResponse()->getBody());
            }
        ]);

        $router->map('error', [
            'path' => '/error',
            'target' => function ($request, $response) {
                throw new HttpException("This is an error!!");
                
            }
        ]);

        $router->map('routes', [
            'path' => '/routes',
            'target' => function ($request, $response) use ($router) {
                $this->assertEquals('http://domain.com/', $router->getUrl('index'));
                $this->assertEquals('http://domain.com/post', $router->getUrl('post'));
                $this->assertEquals('http://domain.com/get/post', $router->getUrl('get-post'));
                $this->assertEquals('http://domain.com/put/34', $router->getUrl('put', ['id' => '34']));
                $this->assertEquals('http://domain.com/put/34?name=oscar', $router->getUrl('put', ['id' => '34', 'name' => 'oscar']));
            }
        ]);

        $router->setError(function ($request, $response) {
            $error = $request->attributes->get('error');

            return 'Error '.$error->getCode().'/'.$error->getMessage();
        });

        $stack = new MiddlewareStack();
        $stack->push($router);

        $stack->run(new Request('http://domain.com'), new Response);
        $this->assertEquals('This is the index', (string) $stack->getResponse()->getBody());

        $stack->run(new Request('/'), new Response);
        $this->assertEquals('This is the index', (string) $stack->getResponse()->getBody());

        $stack->run(new Request('http://domain.com/post', 'POST'), new Response);
        $this->assertEquals('This is POST', (string) $stack->getResponse()->getBody());

        $stack->run(new Request('http://domain.com/put/23', 'PUT'), new Response);
        $this->assertEquals('This is PUT/23', (string) $stack->getResponse()->getBody());

        $stack->run(new Request('http://domain.com/put/2.3', 'PUT'), new Response);
        $this->assertEquals('Error 404/Not found', (string) $stack->getResponse()->getBody());

        $stack->run(new Request('http://domain.com/subrequest'), new Response);
        $this->assertEquals('This is a subrequest: This is POST', (string) $stack->getResponse()->getBody());

        $stack->run(new Request('http://domain.com/post', 'GET'), new Response);
        $this->assertEquals('Error 404/Not found', (string) $stack->getResponse()->getBody());

        $stack->run(new Request('http://domain.com/error', 'GET'), new Response);
        $this->assertEquals('Error 500/This is an error!!', (string) $stack->getResponse()->getBody());
    }
}
