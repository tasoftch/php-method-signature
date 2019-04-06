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

namespace TASoft\PHP;


use TASoft\PHP\Signature\Cache\CacheInterface;
use TASoft\PHP\Signature\Cache\MemoryCache;
use TASoft\PHP\Signature\ClosureSignature;
use TASoft\PHP\Exception\FunctionNotFoundException;
use TASoft\PHP\Signature\FunctionSignature;
use TASoft\PHP\Signature\MethodSignature;

class SignatureService
{
    private static $signatureService;

    /** @var CacheInterface */
    private $cache;

    /**
     * SignatureService constructor.
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache = NULL)
    {
        $this->cache = $cache;
    }

    public static function getSignatureService(): self {
        if(!self::$signatureService)
            self::$signatureService = new static();
        return self::$signatureService;
    }

    /**
     * @param string $functionName
     * @return FunctionSignature
     * @throws FunctionNotFoundException
     */
    public function getFunctionSignature(string $functionName): FunctionSignature {
        $c = $this->getCache();
        if(!$c->getFunctionSignature($functionName)) {
            try {
                $refl = new \ReflectionFunction($functionName);
                $c->storeFunctionSiguature(
                    FunctionSignature::make(
                        $refl
                    ),
                    $refl
                );
            } catch (\ReflectionException $exception) {
                $e = new FunctionNotFoundException($exception->getMessage(), $exception->getCode(), $exception);
                $e->setFunctionName($functionName);
                throw $e;
            }
        }
        return $c->getFunctionSignature($functionName);
    }

    /**
     * @param string $objectClass
     * @param string $methodName
     * @return MethodSignature
     * @throws FunctionNotFoundException
     */
    public function getMethodSignature(string $objectClass, string $methodName): MethodSignature {
        $c = $this->getCache();
        if(!$c->getMethodSignature($objectClass, $methodName)) {
            try {
                $refl = new \ReflectionMethod($objectClass, $methodName);
                $c->storeMethodSignature(
                    MethodSignature::make(
                        $refl
                    ),
                    $refl
                );
            } catch (\ReflectionException $exception) {
                $e = new FunctionNotFoundException($exception->getMessage(), $exception->getCode(), $exception);
                $e->setFunctionName("$objectClass::$methodName");
                throw $e;
            }
        }
        return $c->getMethodSignature($objectClass, $methodName);
    }

    /**
     * @param callable $callback
     * @return ClosureSignature
     */
    public function getClosureSignature(callable $callback): ClosureSignature {
        $c = $this->getCache();
        if(!$c->getClosureSignature($callback)) {
            $c->storeClosureSignature(
                ClosureSignature::make(
                    new \ReflectionFunction($callback)
                ),
                $callback
            );
        }
        return $c->getClosureSignature($callback);
    }

    /**
     * @param $anything
     * @return FunctionSignature|null
     */
    public function getSignature($anything): ?FunctionSignature {
        if (is_string($anything)) {
            if (function_exists($anything))
                return $this->getFunctionSignature($anything);
            $anything = explode("::", $anything);
            if (count($anything) == 2) {
                return $this->getMethodSignature($anything[0], $anything[1]);
            }
        } elseif (is_array($anything)) {
            if (count($anything) == 2) {
                $class = $anything[0];
                if (is_object($class))
                    $class = get_class($class);

                return $this->getMethodSignature($class, $anything[1]);
            }
        } elseif (is_callable($anything)) {
            return $this->getClosureSignature($anything);
        }

        return NULL;
    }

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        if(!$this->cache)
            $this->cache = new MemoryCache();
        return $this->cache;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }
}