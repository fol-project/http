Fol\Http
========

Http library for PHP 5.5 compatible with PSR-7

More info about [PSR-7 http-message](https://github.com/php-fig/http-message)

## Requests

```php
use Fol\Http\Request;

//Create from global values ($_GET, $_POST, $_SERVER, $_FILES, etc..)
$request = Request::createFromGlobals();

//Create a custom request
$request = new Request('http://blog.com/?page=2', 'GET', ['Accept' => 'text/html']);

//Object to manage the url data (host, path, query, fragment, etc)
$request->url;

//Manage the query (alias of $request->url->query):
$request->query;

//Manage the headers
$request->headers

//Manage the cookies (alias of $request->headers->cookies)
$request->cookies

//Manage the body data
$request->data

//Manage the uploaded files
$request->files
```

## Response

```php
use Fol\Http\Response;

//Create a response
$response = new Response('Hello world', 200, ['Content-Type' => 'text/html']);

//Manage the headers
$request->headers

//Manage the cookies (alias of $request->headers->cookies)
$request->cookies
```

## Messages (Requests and Responses)

```php
use Fol\Http\Response;

//Returns the body stream
$body = $response->getBody();

$body->write('More content');

echo $body->getContents();
```
