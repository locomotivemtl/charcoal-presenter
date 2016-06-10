<?php

namespace Charcoal\Tests\Presenter;

class TestModel
{
    public $id = 1;

    public function name()
    {
        return 'James Bond';
    }

    public function neverCalled()
    {
        return 'error';
    }
}
