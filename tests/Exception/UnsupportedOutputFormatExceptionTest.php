<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainTeacherBundle\Exception\UnsupportedOutputFormatException;

/**
 * @internal
 */
#[CoversClass(UnsupportedOutputFormatException::class)]
final class UnsupportedOutputFormatExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new UnsupportedOutputFormatException();
        $this->assertNotNull($exception);
    }

    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new UnsupportedOutputFormatException();
        $this->assertNotNull($exception);
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
