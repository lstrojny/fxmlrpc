<?php
namespace fXmlRpc\Exception;

use ReflectionClass;
use fXmlRpc\Exception\RuntimeException;
use fXmlRpc\Exception\InvalidArgumentException;

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

    /**
     * @dataProvider provideExceptions
     */
    public function testAllExceptionsImplementExceptionInterface($exception)
    {
        $this->assertInstanceOf('fXmlRpc\\Exception\\ExceptionInterface', $exception);
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testAllExceptionsEitherExtendBasicInvalidArgumentException($exception)
    {
        $this->assertTrue(
            $exception instanceof RuntimeException || $exception instanceof InvalidArgumentException,
            'Exception must be based on fXmlRpc specific RuntimeException or InvalidArgumentException'
        );
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testGettingMessage($exception)
    {
        $this->assertSame('message', $exception->getMessage());
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testGettingCode($exception)
    {
        $this->assertSame(100, $exception->getCode());
    }
}
