Fol\Http
========

[![Build Status](https://travis-ci.org/fol-project/http.svg?branch=master)](https://travis-ci.org/fol-project/http)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fol-project/http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fol-project/http/?branch=master)


Http library for PHP 5.5

## Basic usage demo

```php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;

//Init a http middleware stack
$stack = new MiddlewareStack();

//Push some middlewares
$stack->push(function ($request, $response) {
	$response->getBody()->write('Hello world');
});

$stack->push(function ($request, $response) {
	$response->getBody()->setStatus(200);
});

//Run
$stack->run(new Request('http://domain.com'), new Response());

//Send the response to the client browser
$stack->getResponse()->send();
```

## Usage with sessions and routes

Sessions and routes are like any other middleware that you can push to the stack

```php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\MiddlewareStack;

use Fol\Http\Sessions\Native;
use Fol\Http\Routes\Router;


$handler = new Handler(new Request('http://domain.com/about'));

// Register and configure some services, for example, a session
$handler->register('session', function ($handler) {
	return new Native($handler);
});

//Init the router
$router = new Router();

//Add some routes:
$router->map([
	'index' => [
		'path' => '/',
		'target' => function ($request, $response) {
			$response->getBody()->write('This is the index');
		}
	],
	'about' => [
		'path' => '/about',
		'target' => function ($request, $response) {
			$session = $request->attributes->get('session');

			$response->getBody()->write('You are '.$session->get('username'));
		}
	]
]);

//Init the stack
$stack = new MiddlewareStack();

//Push the router and session middleware
$stack->push(new Native());
$stack->push($router);

//Run all
$stack->run(new Request('http://domain.com'), new Response());

//Send the response
$stack->getResponse()->send();
```


## Classes

### Request

Manage the data from a request

```php
use Fol\Http\Request;

//Create from global
$request = Request::createFromGlobals();

//Or custom request
$request = new Request('http://blog.com/?page=2', 'GET', ['Accept' => 'text/html']);

//Object to manage the url data (host, path, query, fragment, etc)
$request->url;

//Manage the query (alias of $request->url->query):
$request->query;

//Manage the headers
$request->headers

//Manage the cookies
$request->cookies

//Manage the body data
$request->data

//Manage the uploaded files
$request->files

//Get the body (streamable)
$body = $response->getBody();
```

### Response

```php
use Fol\Http\Response;

//Create a response
$response = new Response('Hello world', 200, ['Content-Type' => 'text/html']);

//Manage the headers
$request->headers

//Manage the cookies
$request->cookies

//Get the body (streamable)
$body = $response->getBody();
```

### MiddlewareStack

Manages all middlewares in a request/response cycle:

### Sessions

Provides an easy interface to work with sessions. There are two types of sessions:

* Native (use the native implementation of PHP)
* Session (to work with fake or custom sessions)

### Router

Provides a simple router system for MVC.
