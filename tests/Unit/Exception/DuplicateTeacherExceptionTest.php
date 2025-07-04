<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Exception\DuplicateTeacherException;

class DuplicateTeacherExceptionTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new DuplicateTeacherException();
        $this->assertInstanceOf(DuplicateTeacherException::class, $exception);
    }

    public function testExtendsException(): void
    {
        $exception = new DuplicateTeacherException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testWithMessage(): void
    {
        $message = 'Duplicate teacher detected';
        $exception = new DuplicateTeacherException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $code = 1002;
        $exception = new DuplicateTeacherException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    public function testWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new DuplicateTeacherException('Duplicate teacher', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}