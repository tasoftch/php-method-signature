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
 * Local memory cache that allows modification checks as well.
 *
 * @package TASoft\PHP\Signature\Cache
 */
class LocalDynamicMemoryCache extends AbstractDynamicMemoryCache
{
    private $metadata;

    /**
     * Pass all signatures you want to capture
     *
     * @param $metadata
     * @param null $functions
     * @param null $methods
     * @param null $closures
     */
    public function __construct(&$metadata, &$functions = NULL, &$methods = NULL, &$closures = NULL)
    {
        if(!is_array($metadata))
            $metadata = [];

        $this->functions = &$functions;
        $this->methods = &$methods;
        $this->closures = &$closures;
        $this->metadata = &$metadata;
    }

    /**
     * @inheritDoc
     */
    protected function &loadMeta(): array
    {
        $a =& $this->metadata;
        return $a;
    }

    /**
     * @inheritDoc
     */
    protected function storeMeta(array $meta)
    {
        $this->metadata = $meta;
    }

    /**
     * @inheritDoc
     */
    protected function loadFunctionSignature(string $functionName): ?FunctionSignature
    {
        return NULL;
    }

    /**
     * @inheritDoc
     */
    protected function loadMethodSignature(string $objectClass, string $methodName): ?MethodSignature
    {
        return NULL;
    }

    /**
     * @inheritDoc
     */
    protected function loadClosureSignature(\Closure $closure): ?ClosureSignature
    {
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function storeFunctionSiguature(FunctionSignature $signature, \ReflectionFunction $function)
    {
        parent::storeFunctionSiguature($signature, $function);
        parent::store();
    }

    /**
     * @inheritDoc
     */
    public function storeMethodSignature(MethodSignature $signature, $method)
    {
        parent::storeMethodSignature($signature, $method);
        parent::store();
    }
}