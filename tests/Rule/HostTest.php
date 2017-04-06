<?php


namespace vivace\router\tests\Rule;


use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule\Host;

class HostTest extends TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServerRequestInterface
     */
    private function createRequest()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn('asd.domain.ru');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        return $request;
    }

    public function testAssign()
    {
        $request = $this->createRequest();
        $rule = new Host\Pattern('*.domain.ru');
        $this->assertInstanceOf(ServerRequestInterface::class, $rule->apply($request));

        $rule = new Host\Pattern('asd.domain.ru');
        $this->assertInstanceOf(ServerRequestInterface::class, $rule->apply($request));


        $rule = new Host\Pattern('*.*.ru');
        $this->assertInstanceOf(ServerRequestInterface::class, $rule->apply($request));

        $rule = new Host\Pattern('*.*.ru');
        $this->assertInstanceOf(ServerRequestInterface::class, $rule->apply($request));

        $rule = new Host\Pattern('*.*.ru');
        $this->assertInstanceOf(ServerRequestInterface::class, $rule->apply($request));

        $request->expects($this->exactly(2))->method('withAttribute')->willReturn($request);
        $rule = new Host\Pattern('{foo}.{bar}.ru');
        $result = $rule->apply($request);
        $this->assertInstanceOf(ServerRequestInterface::class, $result);
    }

    public function testAssignExceptionLongPattern()
    {
        $request = $this->createRequest();
        $this->expectException(NotApplied::class);
        $rule = new Host\Pattern('*.*.ru.asd');
        $rule->apply($request);
    }

    public function testAssignExceptionShortPattern()
    {
        $request = $this->createRequest();
        $this->expectException(NotApplied::class);
        $rule = new Host\Pattern('*.*');
        $rule->apply($request);
    }
}