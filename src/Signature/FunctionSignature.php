<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TASoft\PHP\Signature;


use TASoft\PHP\Attribute\ArgumentValue;
use TASoft\PHP\Attribute\ReturnValue;

class FunctionSignature implements \Serializable, \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var string
     */
    private $qualifiedName;



    /**
     * @var array|ArgumentValue[]
     */
    private $arguments = [];

    /** @var null|ReturnValue */
    private $returnValue;

    public function getIterator()
    {
        return new \ArrayIterator($this->arguments);
    }


    /**
     * FunctionSignature constructor.
     * @param string $qualifiedName
     */
    public function __construct(string $qualifiedName)
    {
        $this->qualifiedName = $qualifiedName;
    }

    /**
     * @return string
     */
    public function getQualifiedName(): string
    {
        return $this->qualifiedName;
    }

    /**
     * @return array|ArgumentValue[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return ReturnValue|null
     */
    public function getReturnValue(): ?ReturnValue
    {
        return $this->returnValue;
    }

    public function serialize()
    {
        return serialize([
            $this->getQualifiedName(),
            $this->getArguments(),
            $this->getReturnValue()
        ]);
    }

    public function unserialize($serialized)
    {
        list($this->qualifiedName, $this->arguments, $this->returnValue) = unserialize($serialized);
    }

    public static function make(\ReflectionFunctionAbstract $reflection): self {
        $sig = new static( $reflection->getName() );

        $getType = function($type) {
            if($type)
                return new ReturnValue($type->getName(), $type->allowsNull());
            return NULL;
        };

        $sig->returnValue = $getType( $reflection->getReturnType() );

        foreach($reflection->getParameters() as $param) {
            $def = NULL;
            try {
                $def = $param->getDefaultValue();
            } catch (\ReflectionException $exception) {
            }
            $sig->arguments[$param->getName()] = new ArgumentValue($param->getName(), ($t = $param->getType()) ? $t->getName() : NULL, $param->isOptional(), $param->allowsNull(), $def);
        }

        return $sig;
    }

    public function offsetExists($offset)
    {
        if(is_numeric($offset))
            return $offset>=0 && $offset < count($this->arguments);
        return isset($this->arguments[$offset]);
    }

    /**
     * @param mixed $offset
     * @return ArgumentValue|null
     */
    public function offsetGet($offset)
    {
        if($this->offsetExists($offset)) {
            if(is_numeric($offset))
                return array_values($this->arguments)[$offset];
            return $this->arguments[$offset];
        }
        return NULL;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function count()
    {
        return count($this->arguments);
    }
}