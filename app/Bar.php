<?php

namespace App;


class Bar
{

    public function bar()
    {
        $foo = new Foo();
        echo $foo->foo();
    }

}