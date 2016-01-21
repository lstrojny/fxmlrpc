<?php
/**
 * Copyright (C) 2012-2016
 * Lars Strojny, InterNations GmbH <lars.strojny@internations.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace fXmlRpc\Exception;

use ReflectionClass;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testFoo()
    {
        $this->assertTrue(true);
    }

    public function provideExceptions()
    {
        $arguments = array();

        $previousException = new \Exception();
        foreach (glob(__DIR__ . '/../../../src/fXmlRpc/Exception/*Exception.php') as $file) {
            $exceptionClassName = 'fXmlRpc\\Exception\\' . preg_replace('~^.*/(.+?Exception)\.php$~', '$1', $file);
            $exceptionClass = new ReflectionClass($exceptionClassName);
            if ($exceptionClass->isAbstract()) {
                continue;
            }
            $arguments[] = array(new $exceptionClassName('message', 100, $previousException));
        }

        return $arguments;
    }

    /** @dataProvider provideExceptions */
    public function testAllExceptionsImplementExceptionInterface($exception)
    {
        $this->assertInstanceOf('fXmlRpc\\Exception\\ExceptionInterface', $exception);
    }

    /** @dataProvider provideExceptions */
    public function testAllExceptionsEitherExtendBasicInvalidArgumentException($exception)
    {
        $this->assertTrue(
            $exception instanceof RuntimeException || $exception instanceof InvalidArgumentException,
            'Exception must be based on fXmlRpc specific RuntimeException or InvalidArgumentException'
        );
    }

    /** @dataProvider provideExceptions */
    public function testGettingMessage($exception)
    {
        $this->assertSame('message', $exception->getMessage());
    }

    /** @dataProvider provideExceptions */
    public function testGettingCode($exception)
    {
        $this->assertSame(100, $exception->getCode());
    }
}
