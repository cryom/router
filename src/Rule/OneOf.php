<?php


namespace vivace\router\Rule;


use Psr\Http\Message\ServerRequestInterface;
use vivace\router\Exception\NotApplied;
use vivace\router\Rule;

class OneOf implements Rule
{
    /**
     * @var Rule[]
     */
    private $rules;

    public function __construct(Rule ...$rules)
    {
        $this->rules = $rules;
    }

    public function apply(ServerRequestInterface $request): ServerRequestInterface
    {
        foreach ($this->rules as $rule) {
            try {
                return $rule->apply($request);
            } catch (NotApplied $e) {
                //pass
            }
        }
    }

}