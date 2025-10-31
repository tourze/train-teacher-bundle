<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainTeacherBundle\Exception\InvalidReportTypeException;

/**
 * @internal
 */
#[CoversClass(InvalidReportTypeException::class)]
final class InvalidReportTypeExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new InvalidReportTypeException();
        $this->assertNotNull($exception);
    }

    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new InvalidReportTypeException();
        $this->assertNotNull($exception);
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
