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

/**
 * SignatureTest.php
 * php-method-signature
 *
 * Created on 2019-04-06 13:31 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\PHP\Exception\FunctionNotFoundException;
use TASoft\PHP\Signature\Cache\FileCache;
use TASoft\PHP\Signature\Cache\LocalDynamicMemoryCache;
use TASoft\PHP\Signature\Cache\LocalMemoryCache;
use TASoft\PHP\Signature\ClosureSignature;
use TASoft\PHP\Signature\FunctionSignature;
use TASoft\PHP\Signature\MethodSignature;
use TASoft\PHP\Signature\VoidClassConstructorSignature;
use TASoft\PHP\SignatureService;


function myTestFunction(int $args, string $name = "Haha", bool $none = false): FunctionSignature {

}


class SignatureTest extends TestCase
{
    public function testFunctionSignature() {
        $sig = SignatureService::getSignatureService()->getFunctionSignature("myTestFunction");
        $this->assertInstanceOf(FunctionSignature::class, $sig);
        $this->assertEquals(FunctionSignature::class, $sig->getReturnValue()->getValue());

        $this->assertCount(3, $sig->getArguments());

        $this->assertEquals("int", $sig[0]->getValue());
        $this->assertEquals("string", $sig[1]->getValue());
        $this->assertEquals("bool", $sig[2]->getValue());

        $this->assertTrue($sig["name"]->isOptional());
        $this->assertFalse($sig["none"]->allowsNull());
        $this->assertFalse($sig["args"]->allowsNull());
        $this->assertFalse($sig["args"]->isOptional());
    }

    protected function methodToTest(array $arguments, ?int $type, FunctionSignature $fsig = NULL): ?ClosureSignature {

    }


    public function testMethodSignature() {
        $sig = SignatureService::getSignatureService()->getMethodSignature(self::class, 'methodToTest');
        $this->assertEquals(self::class, $sig->getClassName());

        $this->assertCount(3, $sig);
        $this->assertEquals("array", $sig[0]->getValue());
        $this->assertEquals("int", $sig[1]->getValue());
        $this->assertEquals(FunctionSignature::class, $sig[2]->getValue());

        $this->assertEquals("arguments", $sig["arguments"]->getName());
    }

    /**
     * @expectedException \TASoft\PHP\Exception\FunctionNotFoundException
     */
    public function testNonexistingFunction() {
        SignatureService::getSignatureService()->getFunctionSignature("unexistingFunction");
    }

    /**
     * @expectedException \TASoft\PHP\Exception\FunctionNotFoundException
     */
    public function testNonexistingMethod() {
        SignatureService::getSignatureService()->getMethodSignature(self::class, "unexistingFunction");
    }

    public function testClosure() {
        $sig = SignatureService::getSignatureService()->getClosureSignature(function(int $argc, array $argv): bool {
        });

        $this->assertCount(2, $sig);
        $this->assertEquals("int", $sig[0]->getValue());
        $this->assertEquals("array", $sig[1]->getValue());

        $this->assertEquals("argv", $sig["argv"]->getName());
    }

    public function testCahced() {
        $service = new SignatureService(new LocalMemoryCache($f, $m, $c));
        $sig = $service->getFunctionSignature("myTestFunction");

        $this->assertEquals(['myTestFunction' => $sig], $f);
        $this->assertNull($m);
        $this->assertNull($c);
    }

    public function testDynamicalCache() {
        $mod = filemtime(__FILE__);

        $service = new SignatureService(new LocalDynamicMemoryCache($meta, $f, $m, $c));
        $sig1 = $service->getFunctionSignature("myTestFunction");

        $this->assertEquals($mod, $meta["f"]["myTestFunction"][1]);
        $this->assertSame($sig1, $service->getFunctionSignature("myTestFunction"));

        // Change the file
        touch(__FILE__, time() - 2);

        $this->assertNotEquals($mod, filemtime("Tests/SignatureTest.php"));

        $this->assertNotSame($sig1, $service->getFunctionSignature("myTestFunction"));
        $this->assertEquals($sig1, $service->getFunctionSignature("myTestFunction"));
    }

    public function testAnythingSignature()
    {
        $service = new SignatureService(new LocalMemoryCache($f, $m, $c));

        // Functions
        $sig = $service->getSignature("strlen");
        $this->assertEquals(FunctionSignature::class, get_class($sig));
        $this->assertEquals("strlen", $sig->getQualifiedName());

        $sig = $service->getSignature("SignatureTest::options");
        $this->assertEquals(MethodSignature::class, get_class($sig));
        $this->assertEquals("options", $sig->getQualifiedName());
        $this->assertEquals("SignatureTest", $sig->getClassName());

        $sig = $service->getSignature(["SignatureTest", "options"]);
        $this->assertEquals(MethodSignature::class, get_class($sig));
        $this->assertEquals("options", $sig->getQualifiedName());
        $this->assertEquals("SignatureTest", $sig->getClassName());

        $sig = $service->getSignature([$this, "methodToTest"]);
        $this->assertEquals(MethodSignature::class, get_class($sig));
        $this->assertEquals("methodToTest", $sig->getQualifiedName());
        $this->assertEquals("SignatureTest", $sig->getClassName());

        $sig = $service->getSignature(function () {
        });

        $this->assertEquals(ClosureSignature::class, get_class($sig));
    }

    public static function options()
    {
    }

    public function testClassNameSignature()
    {
        $service = new SignatureService();
        $sig = $service->getSignature(MySpecialClass::class);

        $this->assertEquals(MySpecialClass::class, $sig->getClassName());
        $this->assertEquals("__construct", $sig->getQualifiedName());

        $this->assertEquals("_123", $sig[0]);
    }

    public function testClassNameSignature2()
    {
        $service = new SignatureService();
        $sig = $service->getSignature(MySpecialClassWithoutConstructor::class);

        $this->assertEquals(MySpecialClassWithoutConstructor::class, $sig->getClassName());
        $this->assertEquals("__construct", $sig->getQualifiedName());

        $this->assertCount(0, $sig);
    }

    public function testInvokableSignature()
    {
        $service = new SignatureService();
        $sig = $service->getSignature(new class
        {
            public function __invoke(MySpecialClass $specialClass, int $flags = NULL)
            {
            }
        });

        $this->assertEquals("__invoke", $sig->getQualifiedName());
        $this->assertEquals('specialClass', $sig[0]);
        $this->assertTrue($sig[1]->isOptional());
        $this->assertTrue($sig['flags']->allowsNull());
    }

    public function testOptionalSignature()
    {
        $service = new SignatureService();
        $sig = $service->getSignature(function (int $argument, string $text, $options = 13) {
        });

        $this->assertEquals('argument', $sig[0]);
        $this->assertNull($sig[1]->getDefaultValue());
        $this->assertEquals(13, $sig[2]->getDefaultValue());
    }

    public function testSubclassWithoutConstructor()
    {
        $service = new SignatureService();
        $sig = $service->getSignature(MySpecialSubclassWithoutConstructor::class);

        $this->assertInstanceOf(VoidClassConstructorSignature::class, $sig);
        $this->assertEquals(MySpecialSubclassWithoutConstructor::class, $sig->getClassName());
        $this->assertEquals("__construct", $sig->getQualifiedName());

        $this->assertCount(0, $sig);
    }

    public function testSubclassWithConstructor()
    {
        $service = new SignatureService();
        $sig = $service->getSignature(MySpecialSubClass::class);

        $this->assertEquals(MySpecialSubClass::class, $sig->getClassName());
        $this->assertEquals("__construct", $sig->getQualifiedName());

        $this->assertCount(2, $sig);
    }
}

class MySpecialClass
{
    public function __construct(int $_123, string $abc)
    {
    }
}

class MySpecialClassWithoutConstructor
{

}


class MySpecialSubclassWithoutConstructor extends MySpecialClassWithoutConstructor
{
}


class MySpecialSubClass extends MySpecialClass
{

}