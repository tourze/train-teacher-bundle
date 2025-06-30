<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainTeacherBundle\Command\EvaluationReminderCommand;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestKernel;

class EvaluationReminderCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected static function getKernelClass(): string
    {
        return IntegrationTestKernel::class;
    }

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('teacher:evaluation:reminder');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithDryRun(): void
    {
        $this->commandTester->execute([
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('预览模式', $output);
        $this->assertStringContainsString('没有需要发送的评价提醒', $output);
    }

    public function testExecuteWithSpecificTeacherId(): void
    {
        $this->commandTester->execute([
            '--teacher-id' => 'nonexistent-teacher',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有需要发送的评价提醒', $output);
    }

    public function testExecuteWithEvaluationType(): void
    {
        $this->commandTester->execute([
            '--evaluation-type' => 'student',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有需要发送的评价提醒', $output);
    }

    public function testExecuteWithDaysOverdue(): void
    {
        $this->commandTester->execute([
            '--days-overdue' => '14',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有需要发送的评价提醒', $output);
    }

    public function testExecuteWithBatchSize(): void
    {
        $this->commandTester->execute([
            '--batch-size' => '10',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有需要发送的评价提醒', $output);
    }

    public function testExecuteWithForce(): void
    {
        $this->commandTester->execute([
            '--force' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有需要发送的评价提醒', $output);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('teacher:evaluation:reminder', EvaluationReminderCommand::NAME);
    }
}