<?php

namespace DrewM\SlimCommander;

class Command
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function t1($args)
    {
        return true;
    }

    public function t2($args)
    {
        return $args['name'];
    }

    public function t3($args)
    {
        return $args['arg_0'];
    }

    public function t4($args)
    {
        return $args['arg_1'];
    }
}