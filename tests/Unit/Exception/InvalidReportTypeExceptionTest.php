<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Exception\InvalidReportTypeException;

class InvalidReportTypeExceptionTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new InvalidReportTypeException();
        $this->assertInstanceOf(InvalidReportTypeException::class, $exception);
    }

    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new InvalidReportTypeException();
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testWithMessage(): void
    {
        $message = 'Invalid report type';
        $exception = new InvalidReportTypeException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $code = 1004;
        $exception = new InvalidReportTypeException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    public function testWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new InvalidReportTypeException('Invalid report type', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}