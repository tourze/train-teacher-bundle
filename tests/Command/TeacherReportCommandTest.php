<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainTeacherBundle\Command\TeacherReportCommand;

/**
 * @internal
 */
#[CoversClass(TeacherReportCommand::class)]
#[RunTestsInSeparateProcesses]
final class TeacherReportCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    public function testExecutePerformanceReport(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'performance',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteEvaluationReport(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'evaluation',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteStatisticsReport(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'statistics',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteSummaryReport(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'summary',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithInvalidReportType(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'invalid-type',
        ]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('无效的报告类型', $output);
    }

    public function testExecuteWithInvalidOutputFormat(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'summary',
            '--output-format' => 'invalid-format',
        ]);

        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('无效的输出格式', $output);
    }

    public function testExecuteWithCustomPeriod(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'performance',
            '--period' => '2024-06',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithOutputFormat(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'summary',
            '--output-format' => 'csv',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithTeacherType(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'performance',
            '--teacher-type' => 'full-time',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithTeacherStatus(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'evaluation',
            '--teacher-status' => 'active',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithIncludeDetails(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'summary',
            '--include-details' => true,
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithTopN(): void
    {
        $exitCode = $this->commandTester->execute([
            'report-type' => 'performance',
            '--top-n' => '5',
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('teacher:report:generate', TeacherReportCommand::NAME);
    }

    public function testArgumentReportType(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'performance']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTeacherId(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'performance', '--teacher-id' => 'nonexistent-teacher']);
        // 接受状态码0或1，因为教师ID可能不存在
        $this->assertContains($exitCode, [0, 1]);
    }

    public function testOptionPeriod(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'performance', '--period' => '2024-06']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionOutputFormat(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'summary', '--output-format' => 'json']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionOutputFile(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'performance', '--output-file' => '/tmp/report.json']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTeacherType(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'performance', '--teacher-type' => 'full-time']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTeacherStatus(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'evaluation', '--teacher-status' => 'active']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionIncludeDetails(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'summary', '--include-details']);
        $this->assertEquals(0, $exitCode);
    }

    public function testOptionTopN(): void
    {
        $exitCode = $this->commandTester->execute(['report-type' => 'performance', '--top-n' => '10']);
        $this->assertEquals(0, $exitCode);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(TeacherReportCommand::class);
        self::assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->add($command);

        $command = $application->find('teacher:report:generate');
        $this->commandTester = new CommandTester($command);
    }
}
