<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainTeacherBundle\Command\PerformanceCalculateCommand;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestKernel;

class PerformanceCalculateCommandTest extends KernelTestCase
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

        $command = $application->find('teacher:performance:calculate');
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
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function testExecuteWithCurrentPeriod(): void
    {
        $this->commandTester->execute([
            'period' => date('Y-m'),
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString(date('Y年m月'), $output);
    }

    public function testExecuteWithCustomPeriod(): void
    {
        $this->commandTester->execute([
            'period' => '2024-06',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('2024年6月', $output);
    }

    public function testExecuteWithInvalidPeriod(): void
    {
        $this->commandTester->execute([
            'period' => 'invalid-period',
            '--dry-run' => true,
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('无效的绩效周期格式', $output);
    }

    public function testExecuteWithSpecificTeacher(): void
    {
        $this->commandTester->execute([
            '--teacher-id' => 'nonexistent-teacher',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function testExecuteWithTeacherType(): void
    {
        $this->commandTester->execute([
            '--teacher-type' => 'full-time',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function testExecuteWithTeacherStatus(): void
    {
        $this->commandTester->execute([
            '--teacher-status' => 'active',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function testExecuteWithBatchSize(): void
    {
        $this->commandTester->execute([
            '--batch-size' => '25',
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function testExecuteWithForce(): void
    {
        $this->commandTester->execute([
            '--force' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到符合条件的教师', $output);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('teacher:performance:calculate', PerformanceCalculateCommand::NAME);
    }
}