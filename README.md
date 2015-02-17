# Fol\Http

Biblioteca Http para PHP 5.5

[![Build Status](https://travis-ci.org/fol-project/http.svg?branch=master)](https://travis-ci.org/fol-project/http)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fol-project/http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fol-project/http/?branch=master)

Aínda que esta dentro do proxecto FOL, pódese usar de xeito independente

## Exemplos

### Uso básico

```php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;

//Inicia unha instancia de MiddlewareStack
$stack = new MiddlewareStack();

//Engade alguns middlewares
$stack->push(function ($request, $response) {
	$response->getBody()->write('Hello world');
});

$stack->push(function ($request, $response) {
	$response->getBody()->setStatus(200);
});

//Executaos
$stack->run(new Request('http://domain.com'), new Response());

//Envía a resposta ao navegador
$stack->getResponse()->send();
```

### Uso con sesións e rutas

As sesións e rutas son como outros "middlewares" que podes engadir ao MiddlewareStack

```php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;

use Fol\Http\Sessions\Native;
use Fol\Http\Routes\Router;

//Inicia o router
$router = new Router();

$router->map([
	'index' => [
		'path' => '/',
		'target' => function ($request, $response) {
			$response->getBody()->write('Esta é a portada');
		}
	],
	'about' => [
		'path' => '/about',
		'target' => function ($request, $response) {
			$session = $request->attributes->get('session');

			$response->getBody()->write('Ola, ti eres '.$session->get('username'));
		}
	]
]);

//Inicia o MiddlewareStack
$stack = new MiddlewareStack();

//Engade a middleware da sesión
$stack->push(new Native());

//E tamén a do router
$stack->push($router);

//Executa
$stack->run(new Request('http://domain.com'), new Response());

//Envía a resposta
$stack->getResponse()->send();
```

## Classes

### Request

Xestiona os datos dunha petición http

```php
use Fol\Http\Request;

//Crear dende as variables globais ($_SERVER, $_FILES, etc)
$request = Request::createFromGlobals();

//Ou podes crear a túa petición personalizada
$request = new Request('http://blog.com/?page=2', 'GET', ['Accept' => 'text/html']);

//Obxecto para acceder aos datos da url (host, path, query, fragment, etc)
$request->url;

//Accede á "query" (alias de $request->url->query):
$request->query;

//Xestiona cabeceiras
$request->headers

//Xestiona cookies
$request->cookies

//Xestiona os datos parseados do body
$request->data

//Xestiona os arquivos subidos
$request->files

//Devolve o body (streamable)
$body = $response->getBody();
```

### Response

```php
use Fol\Http\Response;

//Crea unha resposta
$response = new Response('Hello world', 200, ['Content-Type' => 'text/html']);

//Xestiona as cabeceiras
$request->headers

//Xestiona as cookies
$request->cookies

//Devolve o body (streamable)
$body = $response->getBody();
```

### MiddlewareStack

Xestiona todos os middlewares en todo o ciclo petición/resposta de http. Para entender mellor o concepto dos middlewares, [recomendo ler este artigo](https://mwop.net/blog/2015-01-08-on-http-middleware-and-psr-7.html)

### Sessions

Proporciona unha interface sinxela para traballar con sesións. Hai dous tipos de sesións:

* Native (usa a implementación nativa de PHP)
* Session (para traballar con sesións de proba)

### Router

Proporciona un sinxelo sistema de enrutamento para MVC.
