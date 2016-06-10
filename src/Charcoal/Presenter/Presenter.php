<?php

namespace Charcoal\Presenter;

use \ArrayAccess;
use \InvalidArgumentException;
use \Traversable;

/**
 * Presenter provides a presentation and transformation layer for a "model".
 *
 * It transforms (serializes) any data model (objects or array) into a presentation array, according to a **transformer**.
 *
 * A **transformer** defines the morph rules
 *
 * - A simple array or Traversable object, contain
 */
class Presenter
{
    /**
     * @var callable $transformer
     */
    private $transformer;

    /**
     * @var string $getterPattern
     */
    private $getterPattern;

    /**
     * @param array|Traversable|callable $transformer   The data-view transformation array (or Traversable) object.
     * @param string                     $getterPattern The string pattern to match string with. Must have a single catch-block.
     */
    public function __construct($transformer, $getterPattern = '~{{(\w*?)}}~')
    {
        $this->setTransformer($transformer);
        $this->getterPattern = $getterPattern;
    }

    /**
     * @param array|Traversable|callable $transformer The data-view transformation array (or Traversable) object.
     * @throws InvalidArgumentException If the provided transformer is not valid.
     * @return void
     */
    private function setTransformer($transformer)
    {
        if (is_callable($transformer)) {
            $this->transformer = $transformer;
        } elseif (is_array($transformer) || $transformer instanceof Traversable) {
            $this->transformer = function($model) use ($transformer) {
                return $transformer;
            };
        } else {
            throw new InvalidArgumentException(
                'Transformer must be an array or a Traversable object'
            );
        }
    }

    /**
     * TheT Transformer class is callable. Its purpose is to transform a model (object) into view data.
     *
     * The transformer is set from the constructor.
     *
     * @param mixed $obj The original data (object / model) to transform into view-data.
     * @return array Normalized data, suitable as presentation (view) layer
     */
    public function transform($obj)
    {
        $transformer = $this->transformer;
        return $this->transmogrify($obj, $transformer($obj));
    }

    /**
     * Transmogrify an object into an other structure.
     *
     * @param mixed $obj Source object.
     * @param mixed $val Modifier.
     * @throws InvalidArgumentException If the modifier is not callable, traversable (array) or string.
     * @return mixed The transformed data (type depends on modifier).
     */
    private function transmogrify($obj, $val)
    {
        // Callbacks (lambda or callable) are supported. They must accept the source object as argument.
        if (is_callable($val)) {
            return $val($obj);
        }

        // Arrays or traversables are handled recursively.
        // This also converts / casts any Traversable into a simple array.
        if (is_array($val) || $val instanceof Traversable) {
            $data = [];
            foreach ($val as $k => $v) {
                if (!is_string($k)) {
                    $data[$v] = $this->objectGet($obj, $v);
                } else {
                    $data[$k] = $this->transmogrify($obj, $v);
                }
            }
            return $data;
        }

        // Strings are handled by rendering {{property}} with dynamic object getter.
        if (is_string($val)) {
            return preg_replace_callback($this->getterPattern, function(array $matches) use ($obj) {
                return $this->objectGet($obj, $matches[1]);
            }, $val);
        }

        // Any other
        throw new InvalidArgumentException(
            sprintf(
                'Transmogrify val needs to be callable, traversable (array) or a string. "%s" given.',
                gettype($val)
            )
        );
    }

    /**
     * General-purpose dynamic object "getter".
     *
     * This method tries to fetch a "property" from any type of object (or array),
     * trying to figure out the best possible way:
     *
     * - Method call (`$obj->property()`)
     * - Public property get (`$obj->property`)
     * - Array access, if available (`$obj[property]`)
     * - Returns the property unchanged, otherwise
     *
     * @param mixed  $obj          The model (object or array) to retrieve the property's value from.
     * @param string $propertyName The property name (key) to retrieve from model.
     * @throws InvalidArgumentException If the property name is not a string.
     * @return mixed The object property, if available. The property name, unchanged, if it's not available.
     */
    private function objectGet($obj, $propertyName)
    {
        if (is_callable([$obj, $propertyName])) {
            return $obj->{$propertyName}();
        }

        if (isset($obj->{$propertyName})) {
            return $obj->{$propertyName};
        }

        if ((is_array($obj) || $obj instanceof ArrayAccess) && isset($obj[$propertyName])) {
            return $obj[$propertyName];
        }

        return $propertyName;
    }
}
