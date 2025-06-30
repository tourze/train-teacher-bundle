<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Unit\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainTeacherBundle\Command\EvaluationReminderCommand;
use Tourze\TrainTeacherBundle\Repository\TeacherEvaluationRepository;
use Tourze\TrainTeacherBundle\Repository\TeacherRepository;
use Tourze\TrainTeacherBundle\Service\TeacherService;

/**
 * EvaluationReminderCommand测试
 */
class EvaluationReminderCommandTest extends TestCase
{
    private TeacherService&MockObject $teacherService;
    private TeacherRepository&MockObject $teacherRepository;
    private TeacherEvaluationRepository&MockObject $evaluationRepository;
    private EvaluationReminderCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->teacherService = $this->createMock(TeacherService::class);
        $this->teacherRepository = $this->createMock(TeacherRepository::class);
        $this->evaluationRepository = $this->createMock(TeacherEvaluationRepository::class);
        
        $this->command = new EvaluationReminderCommand(
            $this->teacherService,
            $this->teacherRepository,
            $this->evaluationRepository
        );

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithNoReminders(): void
    {
        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['teacherStatus' => 'active'])
            ->willReturn([]);

        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('没有需要发送的评价提醒', $this->commandTester->getDisplay());
    }

    public function testExecuteWithDryRun(): void
    {
        $this->teacherRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $this->commandTester->execute(['--dry-run' => true]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('预览模式', $this->commandTester->getDisplay());
    }

    public function testExecuteWithSpecificTeacher(): void
    {
        $teacher = $this->createMock(\Tourze\TrainTeacherBundle\Entity\Teacher::class);
        
        $this->teacherService
            ->expects($this->once())
            ->method('getTeacherById')
            ->with('teacher-123')
            ->willReturn($teacher);

        $this->commandTester->execute(['--teacher-id' => 'teacher-123']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(EvaluationReminderCommand::class));
    }
    
    public function testCommandConstantName(): void
    {
        $this->assertEquals('teacher:evaluation:reminder', EvaluationReminderCommand::NAME);
    }
} 