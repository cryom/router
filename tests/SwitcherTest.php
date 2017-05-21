<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 19.04.17
 * Time: 23:46
 */

namespace vivace\router\tests;


use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use vivace\router\Exception\NotFound;
use vivace\router\Switcher;

class SwitcherTest extends TestCase
{

    public function testFind()
    {
        $dispatcher = new Switcher([
            '/' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return 'index';
            },
            'GET/' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return 'GET index';
            },
            'PUT//site.com' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return 'PUT site.com';
            },
            'POST//admin.:site.*' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return $request->getAttribute('site');
            },
            '//site.com' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return 'site.com';
            },
            '/api/user/name' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return '/api/user/name';
            },
            '/api/user/:name' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return $request->getAttribute('name');
            },
            'PATCH/:name' => function (ServerRequestInterface $request, ResponseInterface $response) {
                return $request->getAttribute('name');
            },
        ]);
        $dispatcher->default(function () {
            return 'default';
        });

        $result = $dispatcher->switch($this->getRequest('POST', 'site.ru'), $this->getResponse());
        $this->assertEquals('index', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('GET', 'site.ru'), $this->getResponse());
        $this->assertEquals('GET index', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('PUT', 'site.com'), $this->getResponse());
        $this->assertEquals('PUT site.com', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('POST', 'admin.site.com'), $this->getResponse());
        $this->assertEquals('site', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('POST', 'site.com'), $this->getResponse());
        $this->assertEquals('site.com', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('POST', 'site.com', '/api/user/name'), $this->getResponse());
        $this->assertEquals('/api/user/name', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('POST', 'site.com', '/api/user/albert'), $this->getResponse());
        $this->assertEquals('albert', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('PATCH', 'site.com', '/trololo'), $this->getResponse());
        $this->assertEquals('trololo', $result->getBody()->getContents());

        $result = $dispatcher->switch($this->getRequest('POST', 'site.com', '/undefined'), $this->getResponse());
        $this->assertEquals('default', $result->getBody()->getContents());
    }

    /**
     * @param string $method
     * @param string $host
     * @param string $path
     * @return \PHPUnit_Framework_MockObject_MockObject|ServerRequestInterface
     */
    private function getRequest(string $method, string $host, string $path = '/')
    {
        $attributes = [];
        $url = $this->createMock(UriInterface::class);
        $url->method('getHost')->willReturnCallback(function () use (&$host) {
            return $host;
        });
        $url->method('getPath')->willReturnCallback(function () use (&$path) {
            return $path ?: '/';
        });
        $url->method('withPath')->willReturnCallback(function ($val) use (&$path, $url) {
            $path = $val;
            return $url;
        });
        $url->method('withHost')->willReturnCallback(function ($val) use (&$host, $url) {
            $host = $val;
            return $url;
        });
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn($method);
        $request->method('getUri')->willReturn($url);
        $request->method('withAttribute')->willReturnCallback(function ($name, $value) use (&$attributes, $request) {
            $attributes[$name] = $value;
            return $request;
        });
        $request->method('withUri')->willReturnCallback(function ($value) use (&$url, $request) {
            $url = $value;
            return $request;
        });
        $request->method('getAttribute')->willReturnCallback(function ($name) use (&$attributes) {
            return $attributes[$name] ?? null;
        });
        return $request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    public function getResponse()
    {
        $status = 200;
        $content = '';
        $body = $this->createMock(StreamInterface::class);
        $body->method('write')->willReturnCallback(function ($value) use (&$content, $body) {
            $content = $value;
            return $body;
        });
        $body->method('getContents')->willReturnCallback(function () use (&$content) {
            return $content;
        });
        $response = $this->createMock(ResponseInterface::class);
        $response->method('withStatus')->willReturnCallback(function ($value) use (&$status, $response) {
            $status = $value;
            return $response;
        });
        $response->method('getStatusCode')->willReturnCallback(function () use (&$status) {
            return $status;
        });

        $response->method('getBody')->willReturn($body);
        return $response;
    }

    public function testNestedSwitcher()
    {
        $root = new Switcher([
            '/api' => new Switcher([
                '/' => function () {
                    return '/api/';
                },
                '/:capture' => new Switcher([
                    '/' => function (ServerRequestInterface $request) {
                        return $request->getAttribute('capture');
                    },
                    '//:site.:com/page' => function (ServerRequestInterface $request) {
                        return 'test_' . $request->getAttribute('site')
                            . $request->getAttribute('com')
                            . $request->getAttribute('capture');
                    }
                ]),
                '/page' => function () {
                    return '/api/page';
                },
                '/foo' => new Switcher([
                    '/' => function () {
                        return '/api/foo';
                    },
                    '/bar' => function () {
                        return '/api/foo/bar';
                    }
                ]),
            ]),
            '//mysite' => new Switcher([
                '//ru' => function () {
                    return 'mysite.ru';
                }
            ])
        ]);

        $actual = $root->switch($this->getRequest('GET', 'mysite.ru'), $this->getResponse());
        $this->assertEquals('mysite.ru', $actual->getBody()->getContents());

        $actual = $root->switch($this->getRequest('GET', 'domain.com', '/api/page'), $this->getResponse());
        $this->assertEquals('/api/page', $actual->getBody()->getContents());

        $actual = $root->switch($this->getRequest('GET', 'domain.com', '/api/foo'), $this->getResponse());
        $this->assertEquals('/api/foo', $actual->getBody()->getContents());

        $actual = $root->switch($this->getRequest('GET', 'domain.com', '/api/foo/bar'), $this->getResponse());
        $this->assertEquals('/api/foo/bar', $actual->getBody()->getContents());

        $actual = $root->switch($this->getRequest('GET', 'domain.com', '/api/dynamo'), $this->getResponse());
        $this->assertEquals('dynamo', $actual->getBody()->getContents());

        $actual = $root->switch($this->getRequest('GET', 'domain.com', '/api/dynamo/page'), $this->getResponse());
        $this->assertEquals('test_domaincomdynamo', $actual->getBody()->getContents());
    }

    public function testNotFound()
    {
        $switcher = new Switcher();
        $this->expectException(NotFound::class);
        $switcher->switch($this->getRequest('GET', 'domain.com'), $this->getResponse());
    }
}