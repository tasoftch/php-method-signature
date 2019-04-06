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
 * The abstract dynamic memory cache tries to observe, if the cached functions or methods are changed.
 *
 * @package TASoft\PHP\Signature
 */
abstract class AbstractDynamicMemoryCache extends AbstractMemoryCache
{
    /** @var array Metadata is used to mark modification dates on files. */
    private $meta = [];
    private $markToStore = true;

    /**
     * Load the metadata
     * @return array
     */
    abstract protected function loadMeta(): array;

    /**
     * Stores the metadata
     *
     * @param array $meta
     * @return void
     */
    abstract protected function storeMeta(array $meta);

    /**
     * Load and/or get metadata
     * @return array
     * @internal
     */
    private function _getMeta(): array {
        if(!$this->meta) {
            $this->meta = $this->loadMeta();
        }
        return $this->meta;
    }

    /**
     * Returns true, if the file declaring the requested function was changed since last cache store of this function.
     *
     * @param string $functionName
     * @return bool
     */
    protected function functionDidChange(string $functionName): bool {
        if($f = $this->_getMeta()["f"][$functionName] ?? NULL) {
            list($file, $mod) = $f;
            return $mod != filemtime($file);
        }
        return true;
    }

    /**
     * Returns true, if the file declaring the requested method was changed since last cache store of this method.
     *
     * @param string $objectClass
     * @param string $methodName
     * @return bool
     */
    protected function methodDidChange(string $objectClass, string $methodName): bool {
        if($f = $this->_getMeta()["f"]["$objectClass::$methodName"] ?? NULL) {
            list($file, $mod) = $f;
            return $mod != filemtime($file);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function storeFunctionSiguature(FunctionSignature $signature, \ReflectionFunction $function)
    {
        parent::storeFunctionSiguature($signature, $function);
        $f = $function->getFileName();
        $m = filemtime($f);

        $this->meta["f"][$signature->getQualifiedName()] = [$f, $m];
        $this->markToStore = true;
    }

    /**
     * @inheritDoc
     */
    public function storeMethodSignature(MethodSignature $signature, \ReflectionMethod $method)
    {
        parent::storeMethodSignature($signature, $method);
        $f = $method->getFileName();
        $m = filemtime($f);

        $this->meta["m"][sprintf("%s::%s", $signature->getClassName(), $signature->getQualifiedName())] = [$f, $m];
        $this->markToStore = true;
    }

    /**
     * Always return NULL if the file of declared function changed
     * @inheritDoc
     */
    public function getFunctionSignature(string $functionName): ?FunctionSignature
    {
        return $this->functionDidChange($functionName) ? NULL : parent::getFunctionSignature($functionName);
    }

    /**
     * Always return NULL if the file of declared method changed
     * @inheritDoc
     */
    public function getMethodSignature(string $objectClass, string $methodName): ?MethodSignature
    {
        return $this->methodDidChange($objectClass, $methodName) ? NULL : parent::loadMethodSignature($objectClass, $methodName);
    }

    /**
     * @inheritDoc
     */
    public function store()
    {
        if($this->markToStore)
            $this->storeMeta($this->meta);
        parent::store();
    }
}