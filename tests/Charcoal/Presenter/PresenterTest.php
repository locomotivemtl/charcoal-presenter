<?php

namespace Charcoal\Tests\Presenter;

use \Charcoal\Presenter\Presenter;

use \Charcoal\Tests\Presenter\TestTransformer;
use \Charcoal\Tests\Presenter\TestModel;

/**
 *
 */
class PresenterTest extends \PHPUnit\Framework\TestCase
{
    /**
     *
     */
    public function testClassModel()
    {
        $transformer = [
            'id',
            'name'
        ];

        $expected = [
            'id'=>1,
            'name'=>'James Bond'
        ];

        $presenter = new Presenter($transformer);
        $this->assertEquals($expected, $presenter->transform(new TestModel()));
    
    }

    /**
     *
     */
    public function testArrayTransformer()
    {
        $transformer = [
            'id',
            'name'
        ];

        $model = [
            'id'=>1,
            'name'=>'Foobar',
            'unused'=>42,
            'unused2'=>'allo'
        ];

        $expected = [
            'id'=>1,
            'name'=>'Foobar'
        ];

        $presenter = new Presenter($transformer);
        $this->assertEquals($expected, $presenter->transform($model));
    }

    /**
     *
     */
    public function testCallableTransformer()
    {
        $transformer = function($model) {
            return [
                'id',
                'name',
                'display_date'=>$model['date']->format('Y-m-d')
            ];
        };

        $model = [
            'id'=>1,
            'name'=>'Foobar',
            'date'=>new \Datetime('2015-01-01 10:00:00'),
            'foo'=>'bar'
        ];

        $expected = [
            'id'=>1,
            'name'=>'Foobar',
            'display_date'=>'2015-01-01'
        ];

        $presenter = new Presenter($transformer);
        $this->assertEquals($expected, $presenter->transform($model));
    }

    /**
     *
     */
    public function testClassTransformer()
    {
        $transformer = new TestTransformer();
        $model = [
            'id'=>1,
            'name'=>'Foobar',
            'date'=>new \Datetime('2015-01-01 10:00:00'),
            'foo'=>'bar'
        ];

        $expected = [
            'id'=>1,
            'name'=>'Foobar',
            'display_date'=>'2015-01-01'
        ];
        $presenter = new Presenter($transformer);
        $this->assertEquals($expected, $presenter->transform($model));
    }

    public function testInvalidTransformerThrowsException()
    {
        $this->setExpectedException('\Exception');
        $presenter = new Presenter('foo');
    }

    public function testStringProperty()
    {
        $transformer = [
            'fullname' => '{{firstname}} {{lastname}}',
            'extra' => 'This is an {{extra}} string'
        ];

        $model = [
            'firstname'=>'James',
            'lastname'=>'Bond'
        ];

        $expected = [
            'fullname' => 'James Bond',
            'extra' => 'This is an extra string'
        ];

        $presenter = new Presenter($transformer);
        $this->assertEquals($expected, $presenter->transform($model));
    }

    public function testCallbackProperty()
    {
        $transformer = [
            'initials' => function($model) {
                
                $first = substr($model['firstname'], 0, 1);
                $last = substr($model['lastname'], 0, 1);
                return $first.'. '.$last.'.';
            }
        ];
        $model = [
            'firstname'=>'James',
            'lastname'=>'Bond'
        ];

        $expected = [
            'initials' => 'J. B.'
        ];

        $presenter = new Presenter($transformer);
        $this->assertEquals($expected, $presenter->transform($model));
    }

    public function testInvalidPropertyThrowsException()
    {
        $transformer = [
            'id' => false
        ];

        $model = [
            'id'=>1
        ];

        $presenter = new Presenter($transformer);

        $this->setExpectedException('\Exception');
        $presenter->transform($model);
    }

    public function testCustomStringPattern()
    {
        $transformer = [
            'fullname' => ':firstname: :lastname:'
        ];

        $model = [
            'firstname'=>'James',
            'lastname'=>'Bond'
        ];

        $expected = [
            'fullname' => 'James Bond'
        ];

        $getterPattern = '/:(\w*?):/';

        $presenter = new Presenter($transformer, $getterPattern);
        $this->assertEquals($expected, $presenter->transform($model));
    }
}
