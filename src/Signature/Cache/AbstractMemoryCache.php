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

/**
 * Default implementation to keep loaded signatures in memory
 *
 * @package TASoft\PHP\Signature\Cache
 */
abstract class AbstractMemoryCache implements CacheInterface
{
    protected $functions = [];
    protected $methods = [];
    protected $closures = [];

    /**
     * @inheritDoc
     */
    public function getFunctionSignature(string $functionName): ?FunctionSignature
    {
        if(!isset($this->functions[$functionName])) {
            $this->functions[$functionName] = $this->loadFunctionSignature($functionName) ?: false;
        }
        return $this->functions[$functionName] ?: NULL;
    }

    /**
     * @inheritDoc
     */
    public function getMethodSignature(string $objectClass, string $methodName): ?MethodSignature
    {
        if(!isset($this->methods["$objectClass::$methodName"])) {
            $this->methods["$objectClass::$methodName"] = $this->loadMethodSignature($objectClass, $methodName) ?: false;
        }
        return $this->methods["$objectClass::$methodName"] ?: NULL;
    }

    /**
     * @inheritDoc
     */
    public function getClosureSignature(\Closure $closure): ?ClosureSignature
    {
        $cn = spl_object_hash($closure);
        if(!isset($this->closures[$cn])) {
            $this->closures[$cn] = $this->loadClosureSignature($closure) ?: false;
        }
        return $this->closures[$cn] ?: NULL;
    }

    /**
     * If not in memory yet, this method is called to load from cache
     *
     * @param string $functionName
     * @return FunctionSignature|null
     */
    abstract protected function loadFunctionSignature(string $functionName): ?FunctionSignature;

    /**
     * If not in memory yet, this method is called to load from cache
     *
     * @param string $objectClass
     * @param string $methodName
     * @return MethodSignature|null
     */
    abstract protected function loadMethodSignature(string $objectClass, string $methodName): ?MethodSignature;

    /**
     * If not in memory yet, this method is called to load from cache
     *
     * @param \Closure $closure
     * @return ClosureSignature|null
     */
    abstract protected function loadClosureSignature(\Closure $closure): ?ClosureSignature;

    /**
     * @inheritDoc
     */
    public function storeFunctionSiguature(FunctionSignature $signature, \ReflectionFunction $function)
    {
        $this->functions[$signature->getQualifiedName()] = $signature;
    }

    /**
     * @inheritDoc
     */
    public function storeMethodSignature(MethodSignature $signature, \ReflectionMethod $method)
    {
        $this->methods[sprintf("%s::%s", $signature->getClassName(), $signature->getQualifiedName())] = $signature;
    }

    /**
     * @inheritDoc
     */
    public function storeClosureSignature(ClosureSignature $signature, \Closure $closure)
    {
        $this->closures[ spl_object_hash($closure) ] = $signature;
    }

    /**
     * Stores the cache
     */
    public function store() {
    }
}