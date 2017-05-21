## Synopsis

A simple and fast router, nothing more. The tree-building mechanism is used to quickly find the route.

## Code Example

```php
$switcher = new \vivace\router\Switcher([
    //To check the URL path in a template, the rule must begin with a slash, for example /foo/bar
    '/hello/:message' => function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
        return 'Hello ' . $request->getAttribute('message');
    },
    '/' => function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
        return 'Index Page';
    },
    'POST/user' => function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
        return 'Create user';
    },
    'GET//:app.mysite.com/user/:id ' => function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
        $app = $request->getAttribute('app');
        $id = $request->getAttribute('id');
        return "Request to $app for getting user by id=$id";
    },
    //domain rules should started with double slashes `//`
    '//admin.mysite.com' => function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
        return 'Admin page';
    },
    //It supports nested in one another
    '/api' => new \vivace\router\Switcher([
        '/v1' => function () {
            return 'Nested switcher';
        },
        '/v2' => new \vivace\router\Switcher([
            '/' => function () {
                return 'Deep nested switcher';
            }
        ])
    ]),
    'OPTIONS' => function(){
        return 'Request with OPTIONS method.';
    }
]);
```
```php
//$request and $response is objects, which implementation of psr-7, this library not contain implementation psr-7, you should use third-party libraries, which do it.
$response = $switcher->switch($request, $response);
echo $response->getBody()->getContents();
```
## Motivation

The goal was to create a fast router with support for routing over the host, capturing variables, embedding. Compatibility with PSR-7 was also required.

## Installation

```php composer.phar require vivace/router```

## API Reference

### Switcher::case(string $pattern, callable $handler)

Add handler, which will called if request matched  by pattern successfully.

### Switcher::switch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface

Find and call handler for request

### Switcher::default(callable $handler)

Add default handler, for case when route not matched.

## Tests
##### Via local installed php 

phpunit --testsuite=unit --coverage-html=./tests/coverage/

##### Via docker-compose

docker-compose run --rm phpunit --testsuite=unit --coverage-html=./tests/coverage/

## Contributors

Albert Sultanov <bert.sultanov.contact@gmail.com>

## License

Copyright (c) 2017 Albert Sultanov

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
