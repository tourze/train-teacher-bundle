<?php

declare(strict_types=1);

namespace Tourze\TrainTeacherBundle\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainTeacherBundle\Command\TeacherDataSyncCommand;
use Tourze\TrainTeacherBundle\Tests\Integration\IntegrationTestKernel;

class TeacherDataSyncCommandTest extends KernelTestCase
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

        $command = $application->find('teacher:data:sync');
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
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function testExecuteBasicSync(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('教师数据同步', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function testExecuteWithFixData(): void
    {
        $this->commandTester->execute([
            '--fix-data' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('预览模式', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function testExecuteWithCheckDuplicates(): void
    {
        $this->commandTester->execute([
            '--check-duplicates' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('检查重复数据', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function testExecuteWithUpdateStatus(): void
    {
        $this->commandTester->execute([
            '--update-status' => true,
            '--dry-run' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('更新教师状态', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function testExecuteWithAllOptions(): void
    {
        $this->commandTester->execute([
            '--dry-run' => true,
            '--fix-data' => true,
            '--check-duplicates' => true,
            '--update-status' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('预览模式', $output);
        $this->assertStringContainsString('检查重复数据', $output);
        $this->assertStringContainsString('更新教师状态', $output);
        $this->assertStringContainsString('数据同步检查完成', $output);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertEquals('teacher:data:sync', TeacherDataSyncCommand::NAME);
    }
}