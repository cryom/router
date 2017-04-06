<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 31.03.17
 * Time: 14:16
 */

namespace vivace\router\Rule\Method;


class Get extends Equal
{
    public function __construct()
    {
        parent::__construct('GET');
    }
}