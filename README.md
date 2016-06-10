Charcoal Presenter
==================

The missing layer between models and views. The Presenter takes any _data model_ (objects or arrays) and serializes them into a presentation array according to a _transformer_.

# Usage

Simplest usage, with a simple array transformer:

```php
$presenter = new Presenter([
    'id',
    'name',
    'display_date'
]);

$model = $factory->create(Model::class);
$viewData = $presenter->transform($model);
```

A callable is preferred if operations on objects are required:

The callable signature must `array: callable(mixed $model)`.

```php
$presenter = new Presenter(function($model) {
    return [
        'id',
        'name',
        'display_date' => $model->date->format('Y-m-d')
    ];
});

$model = $factory->create(Model::class);
$viewData = $presenter->transform($model);
```

Common transformers (or customizable transformers, shown below) should be self-contained inside their own `Callable` classes:

```php

class MyTransformer
{
    /**
     * @var string $dateFormat
     */
    private $dateFormat;
    
    /**
     * @param string $dateFormat The date format.
     */
    public function __construct($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @param mixed $model The model to transform.
     * @return array
     */
    public function __invoke($model)
    {
        $displayDate = $obj->date->format($this->dateFormat);
        return [
            'id',
            'name',
            'display_date'=>$displayDate
        ];
    }
}

$presenter = new Presenter(new MyTransformer('Y-m-d'));

$model = $factory->create(Model::class);
$viewData = $presenter->transform($model);
```
