<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 31.03.17
 * Time: 14:16
 */

namespace vivace\router\Rule;


class Get extends Method
{
    public function __construct()
    {
        parent::__construct('GET');
    }
}