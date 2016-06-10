<?php

namespace Charcoal\Tests\Presenter;

class TestTransformer
{
    public function __invoke($model)
    {
        return [
            'id',
            'name',
            'display_date'=>$model['date']->format('Y-m-d')
        ];
    }
}
