<?php


namespace vivace\router;


use vivace\router\Rule\AllOf;
use vivace\router\Rule\OneOf;

class DDD
{
    /** @var  Rule */
    private $condition;

    public function and (Rule $condition)
    {
        if (!$this->condition instanceof AllOf) {
            $this->condition = new AllOf($this->condition);
        }
        $this->condition->append($condition);

    }

    public function or (Rule $condition)
    {
        if (!$this->condition instanceof OneOf) {
            $this->condition = new OneOf($this->condition);
        }
        $this->condition->append($condition);
    }
}