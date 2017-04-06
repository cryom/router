<?php

namespace vivace\router\Rule\Method;


class Post extends Equal
{
    public function __construct()
    {
        parent::__construct('POST');
    }
}