<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainTeacherBundle\Exception\PerformanceNotFoundException;

/**
 * @internal
 */
#[CoversClass(PerformanceNotFoundException::class)]
final class PerformanceNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new PerformanceNotFoundException();
        $this->assertNotNull($exception);
    }

    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new PerformanceNotFoundException();
        $this->assertNotNull($exception);
    }

    public function testWithMessage(): void
    {
        $message = 'Performance not found';
        $exception = new PerformanceNotFoundException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $code = 1005;
        $exception = new PerformanceNotFoundException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    public function testWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new PerformanceNotFoundException('Performance not found', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
