<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Exception\TeacherNotFoundException;

class TeacherNotFoundExceptionTest extends TestCase
{
    public function testIsInstantiable(): void
    {
        $exception = new TeacherNotFoundException();
        $this->assertInstanceOf(TeacherNotFoundException::class, $exception);
    }

    public function testExtendsException(): void
    {
        $exception = new TeacherNotFoundException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testWithMessage(): void
    {
        $message = 'Teacher not found';
        $exception = new TeacherNotFoundException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testWithCode(): void
    {
        $code = 1006;
        $exception = new TeacherNotFoundException('', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    public function testWithPrevious(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new TeacherNotFoundException('Teacher not found', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}