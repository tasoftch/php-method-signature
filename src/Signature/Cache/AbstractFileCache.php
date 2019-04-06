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

abstract class AbstractFileCache extends AbstractDynamicMemoryCache
{
    /** @var string */
    private $filename;
    private $fileContents;
    /** @var bool */
    private $readonly;

    /**
     * FileCache constructor.
     * @param string $filename
     */
    public function __construct(string $filename, bool $readonly = false)
    {
        $this->filename = $filename;
        $this->readonly = $readonly;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }



    protected function loadMeta(): array
    {
        return $this->getFileContents()["meta"] ?? [];
    }

    protected function storeMeta(array $meta)
    {
        $this->getFileContents();
        $this->fileContents["meta"] = $meta;
    }

    public function store()
    {
        if($this->isReadonly() == false) {
            parent::store();
            $this->storeFileContent($this->fileContents);
        }
    }

    public function getFileContents() {
        if($this->fileContents === NULL) {
            $this->fileContents = $this->loadFileContent();
        }
        return $this->fileContents;
    }

    abstract protected function loadFileContent(): array;

    abstract protected function storeFileContent(array $content);

    protected function loadFunctionSignature(string $functionName): ?FunctionSignature
    {
        if($data = $this->getFileContents()["functions"][$functionName] ?? NULL) {
            if(is_string($data)) {
                return unserialize($data);
            }
        }
        return NULL;
    }

    protected function loadMethodSignature(string $objectClass, string $methodName): ?MethodSignature
    {
        if($data = $this->getFileContents()["methods"][sprintf("%s::%s", $objectClass, $methodName)] ?? NULL) {
            if(is_string($data)) {
                return unserialize($data);
            }
        }
        return NULL;
    }

    public function storeFunctionSiguature(FunctionSignature $signature, \ReflectionFunction $function)
    {
        parent::storeFunctionSiguature($signature, $function);
        $this->getFileContents();
        $this->fileContents["functions"][$signature->getQualifiedName()] = serialize($signature);
    }

    public function storeMethodSignature(MethodSignature $signature, \ReflectionMethod $method)
    {
        parent::storeMethodSignature($signature, $method);
        $this->getFileContents();
        $this->fileContents["methods"][sprintf("%s::%s", $signature->getClassName(), $signature->getQualifiedName())] = serialize($signature);
    }


    protected function loadClosureSignature(\Closure $closure): ?ClosureSignature
    {
        // Caching closures is not supported by this version of file caching
        return NULL;
    }
}