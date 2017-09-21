<?php

if (!function_exists('config'))
{
    /**
     * @param string $name
     *
     * @return \Bavix\Slice\Slice
     */
    function config(string $name)
    {
        global $builder;

        return $builder->config()->get($name);
    }
}

if (!function_exists('factory'))
{
    /**
     * @return \Bavix\Processors\Factory
     */
    function factory()
    {
        global $builder;

        return $builder->factory();
    }
}
