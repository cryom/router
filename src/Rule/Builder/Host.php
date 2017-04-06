<?php


namespace vivace\router\Rule\Builder;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;

class Host implements Rule
{
    /** @var  Rule|null */
    private $byEqual;
    /** @var  Rule|null */
    private $byMatch;
    /** @var  Rule|null */
    private $byPattern;

    public function equal(string $host): Host
    {
        $rule = new Rule\Host\Equal($host);
        if (!$this->byEqual) {
            $this->byEqual = $rule;
        } elseif (!$this->byEqual instanceof Rule\OneOf) {
            $this->byEqual = new Rule\OneOf($this->byEqual, $rule);
        } else {
            $this->byEqual->append($rule);
        }
        return $this;
    }

    public function match(string $expression): Host
    {
        $rule = new Rule\Host\Match($expression);
        if (!$this->byMatch) {
            $this->byMatch = $rule;
        } elseif (!$this->byMatch instanceof Rule\OneOf) {
            $this->byMatch = new Rule\OneOf($this->byMatch, $rule);
        } else {
            $this->byMatch->append($rule);
        }
        return $this;
    }

    public function pattern(string $pattern): Host
    {
        $rule = new Rule\Host\Pattern($pattern);
        if (!$this->byPattern) {
            $this->byPattern = $rule;
        } elseif (!$this->byPattern instanceof Rule\OneOf) {
            $this->byPattern = new Rule\OneOf($this->byPattern, $rule);
        } else {
            $this->byPattern->append($rule);
        }
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     * @throws NotApplied
     */
    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        try {
            if ($this->byEqual) {
                $request = $this->byEqual->apply($request);
            }
            if ($this->byMatch) {
                $request = $this->byMatch->apply($request);
            }
            if ($this->byPattern) {
                $request = $this->byPattern->apply($request);
            }
        } catch (NotApplied $e) {
            throw new NotApplied('Rule not applied', $e);
        }

        return $request;
    }
}