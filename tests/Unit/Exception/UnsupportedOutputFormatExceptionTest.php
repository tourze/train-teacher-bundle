<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Exception\UnsupportedOutputFormatException;

class UnsupportedOutputFormatExceptionTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new UnsupportedOutputFormatException();
        $this->assertInstanceOf(UnsupportedOutputFormatException::class, $exception);
    }

    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new UnsupportedOutputFormatException();
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testWithMessage(): void
    {
        $message = 'Unsupported output format';
        $exception = new UnsupportedOutputFormatException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $code = 1007;
        $exception = new UnsupportedOutputFormatException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    public function testWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new UnsupportedOutputFormatException('Unsupported output format', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}