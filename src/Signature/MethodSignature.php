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


class MethodSignature extends FunctionSignature
{
    /** @var string */
    protected $className;

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public static function make(\ReflectionFunctionAbstract $reflection, string $originalClass = NULL): FunctionSignature
    {
        /** @var self $sig */
        $sig = parent::make($reflection);
        if($reflection instanceof \ReflectionMethod)
            $sig->className = $originalClass;
        return $sig;
    }

    public function serialize()
    {
        return serialize([
            $this->className,
            parent::serialize()
            ]);
    }

    public function unserialize($serialized)
    {
        list($this->className, $serialized) = unserialize($serialized);
        parent::unserialize($serialized);
    }


}