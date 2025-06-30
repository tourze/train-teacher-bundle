<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\EntityListener;

use PHPUnit\Framework\TestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\EntityListener\TeacherListener;

/**
 * TeacherListener测试
 */
class TeacherListenerTest extends TestCase
{
    private TeacherListener $listener;

    protected function setUp(): void
    {
        $this->listener = new TeacherListener();
    }



    public function testListenerInstantiation(): void
    {
        $this->assertInstanceOf(TeacherListener::class, $this->listener);
    }
}
