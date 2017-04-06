<?php

namespace vivace\router\tests\Rule\Path;


use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Rule\Path\Pattern;

class PatternTest extends TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServerRequestInterface
     */
    private function createRequest()
    {
        $uri = $this->createMock(\Psr\Http\Message\UriInterface::class);
        $uri->method('getPath')->willReturn('/api/user/albert/sultanov');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('withAttribute')->willReturn($request);
        return $request;
    }

    public function testAssign()
    {
        $req = $this->createRequest();
        $attributes = [];
        $req->expects($this->exactly(2))
            ->method('withAttribute')
            ->willReturnCallback(function ($name, $value) use (&$attributes) {
                $attributes[$name] = $value;
            });
        $rule = new Pattern('/api/user/:firstname/:lastname');
        $this->assertInstanceOf(ServerRequestInterface::class, $rule->apply($req));
        $this->assertArrayHasKey('firstname', $attributes);
        $this->assertArrayHasKey('lastname', $attributes);
        $this->assertEquals('albert', $attributes['firstname']);
        $this->assertEquals('sultanov', $attributes['lastname']);
    }
}