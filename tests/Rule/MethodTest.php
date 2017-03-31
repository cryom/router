<?php


namespace vivace\router\tests\Rule;


use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use vivace\router\Rule\Method;

class MethodTest extends TestCase
{
    public function applyTest()
    {
        $cases = [
            [new Method('get'), 'GET'],
            [new Method('POST'), 'post'],
            [new Method('path'), 'path'],
        ];
        $request = $this->createMock(RequestInterface::class);
        /**
         * @var  $rule
         * @var  $expected
         */
//        foreach ($cases as [$rule, $expected]) {
//            $request->method('getMethod')->willReturn($expected);
//            $rule->
//        }
    }
}