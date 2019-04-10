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

interface CacheInterface
{
    /**
     * Gets the signature for $functionName from cache
     *
     * @param string $functionName
     * @return FunctionSignature|null
     */
    public function getFunctionSignature(string $functionName): ?FunctionSignature;

    /**
     * Stores signature in cache
     *
     * @param FunctionSignature $signature
     * @param \ReflectionFunction $reflection
     * @return void
     */
    public function storeFunctionSiguature(FunctionSignature $signature, \ReflectionFunction $reflection);


    /**
     * Gets method signature from cache
     *
     * @param string $className
     * @param string $methodName
     * @return MethodSignature|null
     */
    public function getMethodSignature(string $className, string $methodName): ?MethodSignature;

    /**
     * Stores method signature in cache
     * If $reflection is a reflection class, the signature is a void constructor method (class without constructor)
     *
     * @param MethodSignature $signature
     * @param \ReflectionMethod|\ReflectionClass $reflection
     * @return mixed
     */
    public function storeMethodSignature(MethodSignature $signature, $reflection);


    /**
     * Gets the signature of closure
     *
     * @param \Closure $closure
     * @return ClosureSignature|null
     */
    public function getClosureSignature(\Closure $closure): ?ClosureSignature;

    /**
     * Stores the signature in cache
     *
     * @param ClosureSignature $signature
     * @param \Closure $closure
     * @return mixed
     */
    public function storeClosureSignature(ClosureSignature $signature, \Closure $closure);
}