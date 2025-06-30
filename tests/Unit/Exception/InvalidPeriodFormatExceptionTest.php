<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Exception\InvalidPeriodFormatException;

class InvalidPeriodFormatExceptionTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new InvalidPeriodFormatException();
        $this->assertInstanceOf(InvalidPeriodFormatException::class, $exception);
    }

    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new InvalidPeriodFormatException();
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testWithMessage(): void
    {
        $message = 'Invalid period format';
        $exception = new InvalidPeriodFormatException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $code = 1003;
        $exception = new InvalidPeriodFormatException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    public function testWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new InvalidPeriodFormatException('Invalid period format', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}