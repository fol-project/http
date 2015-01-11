<?php

//Init a request handler
$handler = new RequestHandler(new Request('http://domain.com'));

//register some services we want, for example, a
$handler->register('session', function ($handler) {
	return new Native($handler);
});

//Send a response
$handler->send(new Response());

/**
 * ============================
 */

//Init a request handler
$handler = new RequestHandler(new Request('http://domain.com'));

//register some services we want, for example, a
$handler->register('session', function ($handler) {
	return new Native($handler);
});

//Init the router passing the handler
$router = new Router($handler, new RouteFactory('namespace'));

$router->map('/', function ($request, $response) {
	$response->getBody()->write('hello world');
});

//Execute subroutes
$response = $router->getResponse($request);

//Run the route
$router->run();













