<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainTeacherBundle\Command\EvaluationReminderCommand;

/**
 * @internal
 */
#[CoversClass(EvaluationReminderCommand::class)]
#[RunTestsInSeparateProcesses]
final class EvaluationReminderCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    public function testExecuteWithDryRun(): void
    {
        $exitCode = $this->commandTester->execute(['--dry-run' => true]);
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('预览模式', $output);
    }

    public function testOptionDryRun(): void
    {
        $exitCode = $this->commandTester->execute(['--dry-run']);
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithSpecificTeacherId(): void
    {
        $exitCode = $this->commandTester->execute(['--teacher-id' => 'nonexistent-teacher', '--dry-run' => true]);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTeacherId(): void
    {
        $exitCode = $this->commandTester->execute(['--teacher-id' => 'test-teacher']);
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithEvaluationType(): void
    {
        $exitCode = $this->commandTester->execute(['--evaluation-type' => 'student', '--dry-run' => true]);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionEvaluationType(): void
    {
        $exitCode = $this->commandTester->execute(['--evaluation-type' => 'student']);
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithDaysOverdue(): void
    {
        $exitCode = $this->commandTester->execute(['--days-overdue' => '14', '--dry-run' => true]);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionDaysOverdue(): void
    {
        $exitCode = $this->commandTester->execute(['--days-overdue' => '7']);
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithBatchSize(): void
    {
        $exitCode = $this->commandTester->execute(['--batch-size' => '10', '--dry-run' => true]);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionBatchSize(): void
    {
        $exitCode = $this->commandTester->execute(['--batch-size' => '20']);
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithForce(): void
    {
        $exitCode = $this->commandTester->execute(['--force' => true, '--dry-run' => true]);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionForce(): void
    {
        $exitCode = $this->commandTester->execute(['--force']);
        $this->assertEquals(0, $exitCode);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('teacher:evaluation:reminder', EvaluationReminderCommand::NAME);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(EvaluationReminderCommand::class);
        self::assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->add($command);

        $command = $application->find('teacher:evaluation:reminder');
        $this->commandTester = new CommandTester($command);
    }
}
