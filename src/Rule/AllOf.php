<?php


namespace vivace\router\Rule;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;

class AllOf implements Rule
{
    /**
     * @var Rule[]
     */
    protected $rules;

    public function __construct(Rule ...$rules)
    {
        $this->rules = $rules;
    }

    public function append(Rule $condition): AllOf
    {
        $this->rules[] = $condition;
        return $this;
    }

    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        foreach ($this->rules as $rule) {
            try {
                $request = $rule->apply($request);
            } catch (NotApplied $e) {
                throw $e;
            }
        }
        return $request;
    }
}