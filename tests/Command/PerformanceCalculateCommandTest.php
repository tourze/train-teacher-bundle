<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainTeacherBundle\Command\PerformanceCalculateCommand;

/**
 * @internal
 */
#[CoversClass(PerformanceCalculateCommand::class)]
#[RunTestsInSeparateProcesses]
final class PerformanceCalculateCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    public function testExecuteWithDryRun(): void
    {
        $exitCode = $this->commandTester->execute([
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('预览模式', $output);
    }

    public function testExecuteWithCurrentPeriod(): void
    {
        $exitCode = $this->commandTester->execute([
            'period' => date('Y-m'),
            '--dry-run' => true,
        ]);

        // 接受状态码0或1
        $this->assertContains($exitCode, [0, 1], '命令执行状态码：' . $exitCode);
    }

    public function testExecuteWithCustomPeriod(): void
    {
        $exitCode = $this->commandTester->execute([
            'period' => '2024-06',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('2024年06月', $output);
    }

    public function testExecuteWithInvalidPeriod(): void
    {
        $exitCode = $this->commandTester->execute([
            'period' => 'invalid-period',
            '--dry-run' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('无效的绩效周期格式', $output);
    }

    public function testExecuteWithSpecificTeacher(): void
    {
        $exitCode = $this->commandTester->execute([
            '--teacher-id' => 'nonexistent-teacher',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        // 测试命令能正常执行即可
    }

    public function testExecuteWithTeacherType(): void
    {
        $exitCode = $this->commandTester->execute([
            '--teacher-type' => 'full-time',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        // 测试命令能正常执行即可
    }

    public function testExecuteWithTeacherStatus(): void
    {
        $exitCode = $this->commandTester->execute([
            '--teacher-status' => 'active',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        // 测试命令能正常执行即可
    }

    public function testExecuteWithBatchSize(): void
    {
        $exitCode = $this->commandTester->execute([
            '--batch-size' => '25',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        // 测试命令能正常执行即可
    }

    public function testExecuteWithForce(): void
    {
        $exitCode = $this->commandTester->execute([
            '--force' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $exitCode);
        // 测试命令能正常执行即可
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('teacher:performance:calculate', PerformanceCalculateCommand::NAME);
    }

    public function testArgumentPeriod(): void
    {
        $exitCode = $this->commandTester->execute(['period' => '2024-06']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTeacherId(): void
    {
        $exitCode = $this->commandTester->execute(['--teacher-id' => 'test-teacher']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTeacherType(): void
    {
        $exitCode = $this->commandTester->execute(['--teacher-type' => 'full-time']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTeacherStatus(): void
    {
        $exitCode = $this->commandTester->execute(['--teacher-status' => 'active']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionForce(): void
    {
        $exitCode = $this->commandTester->execute(['--force']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionDryRun(): void
    {
        $exitCode = $this->commandTester->execute(['--dry-run']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionBatchSize(): void
    {
        $exitCode = $this->commandTester->execute(['--batch-size' => '50']);
        $this->assertEquals(0, $exitCode);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(PerformanceCalculateCommand::class);
        self::assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->add($command);

        $command = $application->find('teacher:performance:calculate');
        $this->commandTester = new CommandTester($command);
    }
}
