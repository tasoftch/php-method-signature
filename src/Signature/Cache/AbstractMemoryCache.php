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

namespace TASoft\PHP\Signature\Cache;


use TASoft\PHP\Signature\ClosureSignature;
use TASoft\PHP\Signature\FunctionSignature;
use TASoft\PHP\Signature\MethodSignature;

abstract class AbstractMemoryCache implements CacheInterface
{
    protected $functions = [];
    protected $methods = [];
    protected $closures = [];

    public function getFunctionSignature(string $functionName): ?FunctionSignature
    {
        if(!isset($this->functions[$functionName])) {
            $this->functions[$functionName] = $this->loadFunctionSignature($functionName) ?: false;
        }
        return $this->functions[$functionName] ?: NULL;
    }

    public function getMethodSignature(string $objectClass, string $methodName): ?MethodSignature
    {
        if(!isset($this->methods["$objectClass::$methodName"])) {
            $this->methods["$objectClass::$methodName"] = $this->loadMethodSignature($objectClass, $methodName) ?: false;
        }
        return $this->methods["$objectClass::$methodName"] ?: NULL;
    }

    public function getClosureSignature(\Closure $closure): ?ClosureSignature
    {
        $cn = spl_object_hash($closure);
        if(!isset($this->closures[$cn])) {
            $this->closures[$cn] = $this->loadClosureSignature($closure) ?: false;
        }
        return $this->closures[$cn] ?: NULL;
    }

    abstract protected function loadFunctionSignature(string $functionName): ?FunctionSignature;

    abstract protected function loadMethodSignature(string $objectClass, string $methodName): ?MethodSignature;

    abstract protected function loadClosureSignature(\Closure $closure): ?ClosureSignature;

    public function storeFunctionSiguature(FunctionSignature $signature, \ReflectionFunction $function)
    {
        $this->functions[$signature->getQualifiedName()] = $signature;
    }

    public function storeMethodSignature(MethodSignature $signature, \ReflectionMethod $method)
    {
        $this->methods[sprintf("%s::%s", $signature->getClassName(), $signature->getQualifiedName())] = $signature;
    }

    public function storeClosureSignature(ClosureSignature $signature, \Closure $closure)
    {
        $this->closures[ spl_object_hash($closure) ] = $signature;
    }

    public function store() {
    }
}