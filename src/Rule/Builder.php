<?php


namespace vivace\router\Rule;

use vivace\router\Rule\Method\Equal;

/**
 * Class Builder
 * @package vivace\router\Rule
 * @property \vivace\router\Rule\Builder\Host $host
 * @property \vivace\router\Rule\Builder\Path $path
 */
class Builder
{
    private const SUB = [
        'host' => Builder\Host::class,
        'path' => Builder\Path::class,
    ];
    /** @var  Method */
    private $method;
    private $host;
    private $path;

    public function method(string ...$methods)
    {
        if (count($methods) > 1) {
            $rules = [];
            foreach ($methods as $method) {
                $rules[] = new Equal($method);
            }
            $rule = new OneOf(...$rules);
        } else {
            $rule = new Equal($methods[0]);
        }
        if (!$this->method) {
            $this->method = $rule;
        } elseif ($this->method instanceof OneOf) {
            if ($rule instanceof OneOf) {
                $this->method->merge($rule);
            } else {
                $this->method->append($rule);
            }
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'host':
                if (!$this->host) {
                    $this->host = new Builder\Host();
                }
                return $this->host;
                break;
            case 'path':
                if (!$this->path) {
                    $this->path = new Builder\Path();
                }
                return $this->path;
                break;
        }
        throw new \InvalidArgumentException("Property {$name} not exists");
    }
}