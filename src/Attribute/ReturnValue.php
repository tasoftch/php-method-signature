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

namespace TASoft\PHP\Attribute;


class ReturnValue extends AttributeValue
{
    /** @var bool */
    private $isVoid;
    /** @var bool */
    private $allowsNull;

    public function getName(): ?string
    {
        return NULL;
    }

    public function __construct(string $value = NULL, bool $allowsNull = false)
    {
        parent::__construct(NULL, $value);
        $this->isVoid = is_null($value);
        $this->allowsNull = $allowsNull;
    }

    /**
     * @return bool
     */
    public function isVoid(): bool
    {
        return $this->isVoid;
    }

    /**
     * @return bool
     */
    public function allowsNull(): bool
    {
        return $this->allowsNull;
    }

    public function serialize()
    {
        return serialize([
            $this->isVoid,
            $this->allowsNull,
            parent::serialize()
        ]);
    }

    public function unserialize($serialized)
    {
        list($this->isVoid, $this->allowsNull, $data) = unserialize($serialized);
        parent::unserialize($data);
    }
}