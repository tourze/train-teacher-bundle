<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\EntityListener;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainTeacherBundle\Entity\Teacher;
use Tourze\TrainTeacherBundle\EntityListener\TeacherListener;

/**
 * TeacherListener测试
 *
 * @internal
 */
#[CoversClass(TeacherListener::class)]
#[RunTestsInSeparateProcesses]
final class TeacherListenerTest extends AbstractIntegrationTestCase
{
    private TeacherListener $listener;

    protected function onSetUp(): void
    {
        $listener = self::getContainer()->get(TeacherListener::class);
        self::assertInstanceOf(TeacherListener::class, $listener);
        $this->listener = $listener;
    }

    public function testListenerInstantiation(): void
    {
        $this->assertInstanceOf(TeacherListener::class, $this->listener);
    }

    public function testPreUpdate(): void
    {
        $teacher = new Teacher();
        $originalUpdateTime = $teacher->getUpdateTime();

        // 等待一小段时间确保时间戳不同
        usleep(1000);

        $this->listener->preUpdate($teacher);

        $this->assertNotEquals($originalUpdateTime, $teacher->getUpdateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $teacher->getUpdateTime());
    }
}
