<?php

namespace App\Controller;


class HelloController
{

    /**
     * @path /hello/index
     * @return string
     */
    public function index()
    {
        return 'Hello Hyperf';
    }

    /**
     * @path /hello/hyperf
     * @return string
     */
    public function hyperf()
    {
        return 'Hyperf Hello';
    }

}