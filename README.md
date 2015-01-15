Fol\Http
========

[![Build Status](https://travis-ci.org/fol-project/http.svg?branch=master)](https://travis-ci.org/fol-project/http)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fol-project/http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fol-project/http/?branch=master)


Http library for PHP 5.5

## Basic usage demo

```php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Handler;

// Init a request handler
$handler = new Handler(new Request('http://domain.com'));

//Prepare a response
$response = $handler->handle(new Response());

//Send it
$response->send();
```

## Usage with sessions and routes

```php
use Fol\Http\Request;
use Fol\Http\Response;
use Fol\Http\Handler;

use Fol\Http\Sessions\Native;
use Fol\Http\Routes\Router;


// Init a request handler
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
			$session = $request->session;

			$response->getBody()->write('You are '.$session->get('username'));
		}
	]
]);

//Run the router with the handler
$response = $router->run($handler);

//Send the response
$response->send();
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

### Handler

Manages a request/response cycle:

* Register services used in the cycle (for example a session, started by the request but closed/modified by the response)
* Prepare the response according to the request (for example, remove the body content of the response on HEAD requests)
* You can define a base url to normalices the scope of the cookies, routes, etc

### Sessions

Provides an easy interface to work with sessions. There are two types of sessions:

* Native (use the native implementation of PHP)
* Session (to work with fake or custom sessions)

### Router

Provides a simple router system for MVC.
