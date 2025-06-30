<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Exception\DuplicateEvaluationException;

class DuplicateEvaluationExceptionTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new DuplicateEvaluationException();
        $this->assertInstanceOf(DuplicateEvaluationException::class, $exception);
    }

    public function testExtendsException(): void
    {
        $exception = new DuplicateEvaluationException();
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