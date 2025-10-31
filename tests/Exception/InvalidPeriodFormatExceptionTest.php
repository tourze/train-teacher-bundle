<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainTeacherBundle\Exception\InvalidPeriodFormatException;

/**
 * @internal
 */
#[CoversClass(InvalidPeriodFormatException::class)]
final class InvalidPeriodFormatExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new InvalidPeriodFormatException();
        $this->assertNotNull($exception);
    }

    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new InvalidPeriodFormatException();
        $this->assertNotNull($exception);
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
