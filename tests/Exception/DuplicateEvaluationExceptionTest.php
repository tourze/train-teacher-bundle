<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException;

/**
 * @internal
 */
#[CoversClass(DuplicateEvaluationException::class)]
final class DuplicateEvaluationExceptionTest extends AbstractExceptionTestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new DuplicateEvaluationException();
        $this->assertNotNull($exception);
    }

    public function testExtendsException(): void
    {
        $exception = new DuplicateEvaluationException();
        $this->assertNotNull($exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testWithMessage(): void
    {
        $message = 'Duplicate evaluation detected';
        $exception = new DuplicateEvaluationException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $code = 1001;
        $exception = new DuplicateEvaluationException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    public function testWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new DuplicateEvaluationException('Duplicate evaluation', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
