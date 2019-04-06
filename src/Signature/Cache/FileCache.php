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

class FileCache extends AbstractDynamicMemoryCache
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

    protected function loadMeta(): array
    {
        return $this->_getContents()["meta"] ?? [];
    }

    protected function storeMeta(array $meta)
    {
        $this->_getContents();
        $this->fileContents["meta"] = $meta;
    }

    public function store()
    {
        parent::store();
        $this->storeFileContent($this->fileContents);
    }


    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    protected function loadFileContent(): array {
        if(file_exists($this->getFilename()))
            return require $this->getFilename();
        return [];
    }

    protected function storeFileContent(array $content) {
        $content = var_export($content, true);
        file_put_contents($this->getFilename(), "<?php\nreturn $content;");
        $this->contentChanged = false;
    }

    private function _getContents() {
        if($this->fileContents === NULL) {
            $this->fileContents = $this->loadFileContent();
        }
        return $this->fileContents;
    }

    public function storeFunctionSiguature(FunctionSignature $signature, \ReflectionFunction $function)
    {
        parent::storeFunctionSiguature($signature, $function);
        $this->_getContents();
        $this->fileContents["functions"][$signature->getQualifiedName()] = serialize($signature);
    }

    public function storeMethodSignature(MethodSignature $signature, \ReflectionMethod $method)
    {
        parent::storeMethodSignature($signature, $method);
        $this->_getContents();
        $this->fileContents["methods"][sprintf("%s::%s", $signature->getClassName(), $signature->getQualifiedName())] = serialize($signature);
    }

    /**
     * @return bool
     */
    public function isReadonly(): bool
    {
        return $this->readonly;
    }


    protected function loadFunctionSignature(string $functionName): ?FunctionSignature
    {
        if($data = $this->_getContents()["functions"][$functionName] ?? NULL) {
            if(is_string($data)) {
                return unserialize($data);
            }
        }
        return NULL;
    }

    protected function loadMethodSignature(string $objectClass, string $methodName): ?MethodSignature
    {
        if($data = $this->_getContents()["methods"][sprintf("%s::%s", $objectClass, $methodName)] ?? NULL) {
            if(is_string($data)) {
                return unserialize($data);
            }
        }
        return NULL;
    }

    protected function loadClosureSignature(\Closure $closure): ?ClosureSignature
    {
        // Closures are dynamically created and can not be stored.
        return NULL;
    }
}